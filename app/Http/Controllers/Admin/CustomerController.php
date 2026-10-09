<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $customers = User::where('role', 'customer')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->withCount(['bookings' => fn ($q) => $q->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])])
            ->withSum(['bookings' => fn ($q) => $q->where('payment_status', 'paid')], 'total_price')
            ->orderByDesc('bookings_count')
            ->paginate(20);

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $user): View
    {
        $bookings = $user->bookings()
            ->with(['room.category', 'addons'])
            ->latest('check_in')
            ->paginate(15);

        $totalSpent = (float) $user->bookings()->where('payment_status', 'paid')->sum('total_price');
        $completedStays = $user->bookings()->where('status', 'checked_out')->count();
        $activeBookings = $user->bookings()->whereIn('status', ['confirmed', 'checked_in'])->count();

        return view('admin.customers.show', compact('user', 'bookings', 'totalSpent', 'completedStays', 'activeBookings'));
    }
}
