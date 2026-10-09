<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
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
            ->where('status', 'checked_in')
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

        // Monthly revenue chart data (last 6 months)
        $revenueChartData = collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = Carbon::now()->subMonths($monthsAgo);

            return [
                'month' => $month->format('M Y'),
                'revenue' => Booking::where('payment_status', 'paid')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_price'),
            ];
        });

        // Top frequent visitors
        $frequentGuests = User::where('role', 'customer')
            ->orderByDesc('visit_count')
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
