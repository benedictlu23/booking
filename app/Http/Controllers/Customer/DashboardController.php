<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $user = auth()->user();

        $activeBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->with(['room.category'])
            ->latest('check_in')
            ->get();

        $pastBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['checked_out', 'cancelled', 'no_show'])
            ->with(['room.category'])
            ->latest('check_out')
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('user', 'activeBookings', 'pastBookings'));
    }

    public function bookings(Request $request): \Illuminate\View\View
    {
        $user = auth()->user();

        $bookings = Booking::where('user_id', $user->id)
            ->with(['room.category', 'addons.addonService'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);

        return view('customer.bookings', compact('bookings', 'user'));
    }

    public function showBooking(Booking $booking): \Illuminate\View\View
    {
        // Ensure this booking belongs to the current user
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['room.category', 'room.amenities', 'addons.addonService', 'discount']);

        return view('customer.booking-detail', compact('booking'));
    }
}
