<?php

namespace App\Http\Controllers\Admin;

use App\Events\BookingCancelled;
use App\Events\CheckInUpdated;
use App\Events\NewBookingReceived;
use App\Http\Controllers\Controller;
use App\Models\AddonService;
use App\Models\Booking;
use App\Models\HousekeepingLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['room.category', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($query) use ($request) {
                $query->where('booking_code', 'like', "%{$request->search}%")
                    ->orWhere('guest_name', 'like', "%{$request->search}%")
                    ->orWhere('guest_email', 'like', "%{$request->search}%");
            }))
            ->when($request->date, fn ($q) => $q->whereDate('check_in', $request->date))
            ->latest()
            ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        $rooms = Room::with('category')->where('is_active', true)->where('status', 'available')->get();
        $customers = User::where('role', 'customer')->get();

        return view('admin.bookings.create', compact('rooms', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'pets' => ['nullable', 'integer', 'min:0'],
            'booking_type' => ['required', 'in:overnight,day_pass'],
            'is_walk_in' => ['boolean'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if (! $room->isAvailableFor(
            Carbon::parse($validated['check_in']),
            Carbon::parse($validated['check_out'])
        )) {
            return back()->withErrors(['room_id' => 'This room is not available for the selected dates.'])->withInput();
        }

        $petFee = ($validated['pets'] ?? 0) > 0 ? $room->pet_fee : 0;

        $booking = Booking::create([
            ...$validated,
            'booking_code' => 'AGRO-'.strtoupper(Str::random(8)),
            'user_id' => $request->user_id ?? null,
            'base_price' => $room->base_price,
            'pet_fee_total' => $petFee,
            'addons_total' => 0,
            'discount_amount' => 0,
            'total_price' => $room->base_price + $petFee,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'is_walk_in' => $request->boolean('is_walk_in', true),
        ]);

        $room->update(['status' => 'occupied']);

        try {
            broadcast(new NewBookingReceived($booking))->toOthers();
        } catch (\Throwable) {
            // Reverb broadcast failure is non-fatal
        }

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} created successfully.");
    }

    public function show(Booking $booking): View
    {
        $booking->load(['room.category', 'user', 'addons.addonService', 'discount']);
        $availableAddons = AddonService::where('is_active', true)->orderBy('sort_order')->get();
        $housekeepers = User::where('role', 'housekeeper')->orderBy('name')->get();
        $allStaff = User::whereIn('role', ['housekeeper', 'desk_staff', 'admin'])->orderBy('name')->get();

        return view('admin.bookings.show', compact('booking', 'availableAddons', 'housekeepers', 'allStaff'));
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        $booking->room->update(['status' => 'occupied']);

        try {
            broadcast(new CheckInUpdated($booking));
        } catch (\Throwable) {
            // Reverb broadcast failure is non-fatal
        }

        // Increment visit counter if the booking has a registered user
        if ($booking->user) {
            $booking->user->increment('visit_count');
            $booking->user->update(['last_visited_at' => now()]);
        }

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function checkOut(Request $request, Booking $booking): RedirectResponse
    {
        $update = [
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ];

        if ($request->boolean('settle_payment') || $request->input('payment_status') === 'paid') {
            $update['payment_status'] = 'paid';
            if ($request->filled('payment_method')) {
                $update['payment_method'] = $request->payment_method;
            }
        }

        $booking->update($update);

        // Set room to housekeeping status — a housekeeper must confirm it's clean before it's available
        $booking->room->update(['status' => 'housekeeping']);

        // Determine assigned housekeeper
        $housekeeper = null;
        if ($request->filled('housekeeper_id')) {
            $housekeeper = User::find($request->housekeeper_id);
        }

        $staffName = $housekeeper ? $housekeeper->name : 'Unassigned';
        $assignedUserId = $housekeeper ? $housekeeper->id : null;
        $notes = $request->filled('housekeeping_notes')
            ? $request->input('housekeeping_notes')
            : "Checked out: {$booking->booking_code}. Please inspect, clean, and confirm ready.";

        // Create a pending housekeeping log so housekeeping staff know to inspect and clean this room
        HousekeepingLog::create([
            'room_id' => $booking->room_id,
            'user_id' => $assignedUserId,
            'staff_name' => $staffName,
            'status' => 'dirty',
            'notes' => $notes,
            'cleaning_timestamp' => now(),
        ]);

        try {
            broadcast(new CheckInUpdated($booking));
        } catch (\Throwable) {
            // Reverb broadcast failure is non-fatal
        }

        $successMsg = $housekeeper
            ? "Guest checked out. Room {$booking->room?->room_number} queued for housekeeping and assigned to {$housekeeper->name}."
            : "Guest checked out. Room {$booking->room?->room_number} queued for housekeeping.";

        return back()->with('success', $successMsg);
    }

    public function settlePayment(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,card,gcash,bank_transfer'],
        ]);

        $booking->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
        ]);

        return back()->with('success', "Payment for booking {$booking->booking_code} marked as settled (Paid).");
    }

    public function addExtraCharge(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'addon_service_id' => ['nullable', 'exists:room_addon_services,id'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $addonService = ! empty($validated['addon_service_id'])
            ? AddonService::find($validated['addon_service_id'])
            : null;

        $itemName = $addonService ? $addonService->name : ($validated['item_name'] ?? 'Extra Service/Item');
        $unitPrice = $addonService ? (float) $addonService->price : (float) ($validated['unit_price'] ?? 0);
        $subtotal = round($unitPrice * (int) $validated['quantity'], 2);

        $booking->addons()->create([
            'addon_service_id' => $addonService?->id,
            'item_name' => $itemName,
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        $newAddonsTotal = (float) $booking->addons()->sum('subtotal');
        $newTotalPrice = max(0, round((float) $booking->base_price + $newAddonsTotal + (float) $booking->pet_fee_total - (float) $booking->discount_amount, 2));

        $booking->update([
            'addons_total' => $newAddonsTotal,
            'total_price' => $newTotalPrice,
        ]);

        return back()->with('success', "Extra charge '{$itemName}' attached to booking {$booking->booking_code}.");
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'cancelled']);

        $booking->room->update(['status' => 'available']);

        try {
            broadcast(new BookingCancelled($booking))->toOthers();
        } catch (\Throwable) {
            // Reverb broadcast failure is non-fatal
        }

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * Resource Timeline Matrix — provides data for the 2D grid view.
     */
    public function matrix(Request $request): View
    {
        $startDate = Carbon::parse($request->get('start', today()))->startOfDay();
        $days = (int) $request->get('days', 7); // 7 or 30 day view
        $days = in_array($days, [7, 30]) ? $days : 7;
        $endDate = $startDate->copy()->addDays($days);

        $rooms = Room::with(['category', 'bookings' => function ($query) use ($startDate, $endDate) {
            $query->whereNotIn('status', ['cancelled', 'no_show'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('check_in', [$startDate, $endDate])
                        ->orWhereBetween('check_out', [$startDate, $endDate])
                        ->orWhere(function ($inner) use ($startDate, $endDate) {
                            $inner->where('check_in', '<=', $startDate)
                                ->where('check_out', '>=', $endDate);
                        });
                });
        }])
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('room_number')
            ->get();

        $dateRange = collect(range(0, $days - 1))->map(fn ($i) => $startDate->copy()->addDays($i));

        return view('admin.bookings.matrix', compact('rooms', 'dateRange', 'startDate', 'days'));
    }
}
