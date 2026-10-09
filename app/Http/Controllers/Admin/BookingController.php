<?php

namespace App\Http\Controllers\Admin;

use App\Events\BookingCancelled;
use App\Events\CheckInUpdated;
use App\Events\NewBookingReceived;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
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

    public function create(): \Illuminate\View\View
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

        broadcast(new NewBookingReceived($booking))->toOthers();

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} created successfully.");
    }

    public function show(Booking $booking): \Illuminate\View\View
    {
        $booking->load(['room.category', 'user', 'addons.addonService', 'discount']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        $booking->room->update(['status' => 'occupied']);

        broadcast(new CheckInUpdated($booking))->toOthers();

        // Increment visit counter if the booking has a registered user
        if ($booking->user) {
            $booking->user->increment('visit_count');
            $booking->user->update(['last_visited_at' => now()]);
        }

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function checkOut(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        $booking->room->update(['status' => 'available']);

        broadcast(new CheckInUpdated($booking))->toOthers();

        return back()->with('success', 'Guest checked out. Room is now available.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'cancelled']);

        $booking->room->update(['status' => 'available']);

        broadcast(new BookingCancelled($booking))->toOthers();

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * Resource Timeline Matrix — provides data for the 2D grid view.
     */
    public function matrix(Request $request): \Illuminate\View\View
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
