<?php

namespace App\Http\Controllers\Housekeeper;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingLog;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HousekeeperController extends Controller
{
    /**
     * Show the housekeeper dashboard: rooms needing attention and pending logs.
     */
    public function index(): View
    {
        // Rooms currently in housekeeping status
        $roomsPendingCleaning = Room::with(['housekeepingLogs' => fn ($q) => $q->latest()->take(1)])
            ->where('status', 'housekeeping')
            ->orderBy('room_number')
            ->get();

        // Open (dirty / in_progress) housekeeping log entries with room and assigned staff
        $pendingLogs = HousekeepingLog::with(['room', 'assignedTo'])
            ->whereIn('status', ['dirty', 'in_progress'])
            ->latest('cleaning_timestamp')
            ->get();

        // Recently completed logs today
        $completedToday = HousekeepingLog::with(['room', 'assignedTo'])
            ->where('status', 'clean')
            ->whereDate('completed_at', today())
            ->latest('completed_at')
            ->take(10)
            ->get();

        return view('housekeeper.dashboard', compact('roomsPendingCleaning', 'pendingLogs', 'completedToday'));
    }

    /**
     * Housekeeper starts cleaning/inspection on an assigned log.
     */
    public function startCleaning(HousekeepingLog $housekeepingLog): RedirectResponse
    {
        $housekeepingLog->update([
            'status' => 'in_progress',
            'user_id' => $housekeepingLog->user_id ?: auth()->id(),
            'staff_name' => $housekeepingLog->user_id ? $housekeepingLog->staff_name : auth()->user()->name,
        ]);

        return back()->with('success', "Started cleaning Room {$housekeepingLog->room?->room_number}. Status updated to In Progress.");
    }

    /**
     * Housekeeper confirms that a room is clean and ready — sets status to available.
     */
    public function confirm(Request $request, HousekeepingLog $housekeepingLog): RedirectResponse
    {
        $notes = $housekeepingLog->notes ?? '';
        if ($request->filled('notes')) {
            $notes = trim($notes.' | Housekeeper note: '.$request->input('notes'));
        }
        $notes = trim($notes.' | Confirmed clean by '.auth()->user()->name.' at '.now()->format('M d, Y h:i A'));

        $housekeepingLog->update([
            'status' => 'clean',
            'staff_name' => auth()->user()->name,
            'user_id' => auth()->id(),
            'completed_at' => now(),
            'notes' => $notes,
        ]);

        // Mark the room as available
        $room = $housekeepingLog->room;
        if ($room && $room->status === 'housekeeping') {
            $room->update(['status' => 'available']);
        }

        return back()->with('success', "Room {$room?->room_number} confirmed clean and marked as Available to occupy.");
    }

    /**
     * Direct clean confirmation for a room currently in housekeeping status without a prior log.
     */
    public function confirmRoomClean(Request $request, Room $room): RedirectResponse
    {
        $notes = $request->input('notes') ?: 'Room inspected and cleared.';
        $notes = trim($notes.' | Confirmed clean by '.auth()->user()->name.' at '.now()->format('M d, Y h:i A'));

        HousekeepingLog::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'staff_name' => auth()->user()->name,
            'status' => 'clean',
            'notes' => $notes,
            'cleaning_timestamp' => now(),
            'completed_at' => now(),
        ]);

        $room->update(['status' => 'available']);

        return back()->with('success', "Room {$room->room_number} confirmed clean and marked as Available to occupy.");
    }
}
