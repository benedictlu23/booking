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
use Illuminate\Support\Facades\Log;
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
        $rooms = Room::with('category')
            ->where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'out_of_service', 'blocked'])
            ->orderBy('category_id')
            ->orderBy('room_number')
            ->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('admin.bookings.create', compact('rooms', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isDayPass = $request->input('booking_type') === 'day_pass';
        if ($isDayPass && $request->filled('check_in')) {
            $cIn = Carbon::parse($request->input('check_in'));
            $request->merge(['check_out' => $cIn->copy()->setTime(17, 0)->format('Y-m-d\TH:i')]);
        }

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => [
                'required',
                'date',
                $isDayPass
                    ? function ($attribute, $value, $fail) use ($request) {
                        try {
                            if (Carbon::parse($value)->toDateString() !== Carbon::parse($request->input('check_in'))->toDateString()) {
                                $fail('For Day Pass, the Check-Out date must match the Check-In date.');
                            }
                        } catch (\Throwable) {
                            $fail('Invalid date format.');
                        }
                    }
                    : 'after:check_in',
            ],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'pets' => ['nullable', 'integer', 'min:0'],
            'booking_type' => ['required', 'in:overnight,day_pass'],
            'is_walk_in' => ['nullable', 'boolean'],
            'payment_status' => ['nullable', 'in:unpaid,paid,partial'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,gcash,bank_transfer'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'check_in_now' => ['nullable', 'boolean'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ], [
            'check_out.after' => 'Check-out date must be after check-in date for overnight stays.',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        $cIn = Carbon::parse($validated['check_in']);
        $cOut = Carbon::parse($validated['check_out']);

        if (! $room->isAvailableFor($cIn, $cOut)) {
            return back()->withErrors(['room_id' => 'This room is not available for the selected dates.'])->withInput();
        }

        $nights = $validated['booking_type'] === 'day_pass' ? 1 : max(1, $cIn->diffInDays($cOut));
        $basePrice = round((float) $room->base_price * $nights, 2);
        $petsCount = (int) ($validated['pets'] ?? 0);
        $petFee = ($petsCount > 0 && $room->allows_pets)
            ? round((float) $room->pet_fee * $nights * $petsCount, 2)
            : 0.0;
        $totalPrice = round($basePrice + $petFee, 2);

        $paymentStatus = $request->input('payment_status', 'unpaid');
        $paymentMethod = $request->input('payment_method', 'cash');

        if ($paymentStatus === 'paid') {
            $amountPaid = $totalPrice;
        } elseif ($request->filled('amount_paid')) {
            $amountPaid = min((float) $request->input('amount_paid'), $totalPrice);
            $paymentStatus = ($amountPaid >= $totalPrice && $totalPrice > 0)
                ? 'paid'
                : ($amountPaid > 0 ? 'partial' : 'unpaid');
        } else {
            $amountPaid = 0.0;
            $paymentStatus = 'unpaid';
        }

        $checkInNow = $request->boolean('check_in_now');
        $bookingStatus = $checkInNow ? 'checked_in' : 'confirmed';

        $booking = Booking::create([
            'room_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'pets' => $petsCount,
            'booking_type' => $validated['booking_type'],
            'booking_code' => 'AGRO-'.strtoupper(Str::random(8)),
            'user_id' => $request->user_id ?: null,
            'base_price' => $basePrice,
            'pet_fee_total' => $petFee,
            'addons_total' => 0,
            'discount_amount' => 0,
            'total_price' => $totalPrice,
            'amount_paid' => $amountPaid,
            'status' => $bookingStatus,
            'checked_in_at' => $checkInNow ? now() : null,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'is_walk_in' => $request->boolean('is_walk_in', true),
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        if ($checkInNow) {
            $room->update(['status' => 'occupied']);
        }

        try {
            $booking->load(['room.category']);
            broadcast(new NewBookingReceived($booking));
        } catch (\Throwable $e) {
            Log::warning('NewBookingReceived broadcast error: '.$e->getMessage());
        }

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} created successfully.");
    }

    public function show(Booking $booking): View
    {
        $booking->load(['room.category', 'room.photos', 'user', 'addons.addonService', 'discount']);
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
            $update['amount_paid'] = $booking->total_price;
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
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $settleAmount = $request->filled('amount')
            ? (float) $request->input('amount')
            : $booking->balance_due;

        $newAmountPaid = round((float) ($booking->amount_paid ?? 0) + $settleAmount, 2);
        $newPaymentStatus = $newAmountPaid >= (float) $booking->total_price ? 'paid' : 'partial';

        $booking->update([
            'amount_paid' => min($newAmountPaid, (float) $booking->total_price),
            'payment_status' => $newPaymentStatus,
            'payment_method' => $validated['payment_method'],
        ]);

        return back()->with('success', "Payment for booking {$booking->booking_code} recorded. Balance: ₱".number_format($booking->fresh()->balance_due, 2));
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
        $currentAmountPaid = (float) ($booking->amount_paid ?? 0);

        if ($currentAmountPaid >= $newTotalPrice && $newTotalPrice > 0) {
            $paymentStatus = 'paid';
        } elseif ($currentAmountPaid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $booking->update([
            'addons_total' => $newAddonsTotal,
            'total_price' => $newTotalPrice,
            'payment_status' => $paymentStatus,
        ]);

        return back()->with('success', "Extra charge '{$itemName}' (₱".number_format($subtotal, 2).') attached to billing. Balance due: ₱'.number_format($booking->fresh()->balance_due, 2));
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'cancelled']);

        $booking->room->update(['status' => 'available']);

        try {
            broadcast(new BookingCancelled($booking));
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
        $days = (int) $request->get('days', 7); // 7, 14, or 30 day view
        $days = in_array($days, [7, 14, 30]) ? $days : 7;
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
