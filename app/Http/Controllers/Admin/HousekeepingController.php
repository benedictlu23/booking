<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingLog;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HousekeepingController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = Room::orderBy('room_number')->get();

        $logs = HousekeepingLog::with(['room', 'assignedTo'])
            ->when($request->room_id, fn ($q) => $q->where('room_id', $request->room_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date, fn ($q) => $q->whereDate('cleaning_timestamp', $request->date))
            ->latest('cleaning_timestamp')
            ->latest('id')
            ->paginate(20);

        return view('admin.housekeeping.index', compact('logs', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'staff_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:clean,dirty,in_progress,inspected'],
            'cleaning_timestamp' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cleaningTimestamp = ! empty($validated['cleaning_timestamp'])
            ? Carbon::parse($validated['cleaning_timestamp'])
            : now();

        $log = HousekeepingLog::create([
            'room_id' => $validated['room_id'],
            'user_id' => auth()->id(),
            'staff_name' => $validated['staff_name'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'cleaning_timestamp' => $cleaningTimestamp,
            'completed_at' => $validated['status'] === 'clean' ? now() : null,
        ]);

        $room = Room::find($validated['room_id']);
        if ($room) {
            if ($validated['status'] === 'clean') {
                if ($room->status === 'housekeeping') {
                    $room->update(['status' => 'available']);
                }
            } elseif (in_array($validated['status'], ['dirty', 'in_progress'])) {
                if ($room->status === 'available') {
                    $room->update(['status' => 'housekeeping']);
                }
            }
        }

        return back()->with('success', "Housekeeping log for Room {$room?->room_number} recorded successfully.");
    }

    public function destroy(HousekeepingLog $housekeeping): RedirectResponse
    {
        $housekeeping->delete();

        return back()->with('success', 'Housekeeping log entry removed.');
    }
}
