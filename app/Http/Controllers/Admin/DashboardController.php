<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Core metrics
        $totalRevenue = Booking::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');

        $totalBookings = Booking::whereMonth('created_at', now()->month)->count();

        $totalRooms = Room::where('is_active', true)->count();

        $occupiedRooms = Room::whereIn('status', ['occupied'])->count();

        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100, 1)
            : 0;

        $todaysCheckIns = Booking::whereDate('check_in', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with(['room', 'user'])
            ->get();

        $todaysCheckOuts = Booking::whereDate('check_out', $today)
            ->whereIn('status', ['checked_in', 'checked_out', 'confirmed'])
            ->with(['room', 'user'])
            ->get();

        $pendingBookings = Booking::where('status', 'pending')
            ->with(['room', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $recentBookings = Booking::with(['room', 'user'])
            ->latest()
            ->take(8)
            ->get();

        // Monthly revenue & bookings trend (last 6 months, using startOfMonth to avoid Carbon day overflow)
        $revenueChartData = collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'month' => $month->format('M Y'),
                'revenue' => (float) Booking::where('payment_status', 'paid')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_price'),
                'bookings_count' => Booking::whereNotIn('status', ['cancelled'])
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        });

        // Top customers ranked by actual bookings and total spend
        $frequentGuests = User::where('role', 'customer')
            ->withCount(['bookings' => fn ($q) => $q->whereNotIn('status', ['cancelled'])])
            ->withSum(['bookings' => fn ($q) => $q->where('payment_status', 'paid')], 'total_price')
            ->orderByDesc('bookings_count')
            ->orderByDesc('bookings_sum_total_price')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue', 'totalBookings', 'totalRooms',
            'occupancyRate', 'occupiedRooms', 'todaysCheckIns',
            'todaysCheckOuts', 'pendingBookings', 'recentBookings',
            'revenueChartData', 'frequentGuests'
        ));
    }
}
