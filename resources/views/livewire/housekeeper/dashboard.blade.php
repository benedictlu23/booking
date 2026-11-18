<?php

use App\Models\HousekeepingLog;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Volt\Component;

new class extends Component {
    public string $filter = 'all'; // all, mine, dirty, in_progress
    public ?int $activeLogIdForNote = null;
    public string $housekeeperNote = '';
    public ?string $flashMessage = null;

    public function startCleaning(int $logId): void
    {
        $log = HousekeepingLog::find($logId);
        if ($log) {
            $user = auth()->user();
            $log->update([
                'status' => 'in_progress',
                'user_id' => $log->user_id ?: $user?->id,
                'staff_name' => $log->user_id ? $log->staff_name : ($user?->name ?? 'Housekeeper'),
            ]);
            $this->flashMessage = "Started cleaning Room {$log->room?->room_number}. Status updated to In Progress.";
        }
    }

    public function openConfirmModal(int $logId): void
    {
        $this->activeLogIdForNote = $logId;
        $this->housekeeperNote = '';
    }

    public function closeConfirmModal(): void
    {
        $this->activeLogIdForNote = null;
        $this->housekeeperNote = '';
    }

    public function confirmClean(int $logId): void
    {
        $log = HousekeepingLog::find($logId);
        if ($log) {
            $user = auth()->user();
            $notes = $log->notes ?? '';
            if (filled($this->housekeeperNote)) {
                $notes = trim($notes.' | Housekeeper note: '.trim($this->housekeeperNote));
            }
            $notes = trim($notes.' | Confirmed clean by '.($user?->name ?? 'Housekeeper').' at '.now()->format('M d, Y h:i A'));

            $log->update([
                'status' => 'clean',
                'staff_name' => $user?->name ?? $log->staff_name,
                'user_id' => $user?->id ?? $log->user_id,
                'completed_at' => now(),
                'notes' => $notes,
            ]);

            if ($log->room && $log->room->status === 'housekeeping') {
                $log->room->update(['status' => 'available']);
            }

            $this->flashMessage = "Room {$log->room?->room_number} confirmed clean and marked as Available to occupy.";
            $this->closeConfirmModal();
        }
    }

    public function confirmRoomClean(int $roomId): void
    {
        $room = Room::find($roomId);
        if ($room) {
            $user = auth()->user();
            $notes = 'Room inspected and cleared. | Confirmed clean by '.($user?->name ?? 'Housekeeper').' at '.now()->format('M d, Y h:i A');

            HousekeepingLog::create([
                'room_id' => $room->id,
                'user_id' => $user?->id,
                'staff_name' => $user?->name ?? 'Housekeeper',
                'status' => 'clean',
                'notes' => $notes,
                'cleaning_timestamp' => now(),
                'completed_at' => now(),
            ]);

            $room->update(['status' => 'available']);
            $this->flashMessage = "Room {$room->room_number} confirmed clean and marked as Available.";
        }
    }

    public function getRoomsPendingCleaningProperty(): EloquentCollection
    {
        return Room::with(['housekeepingLogs' => fn ($q) => $q->latest()->take(1)])
            ->where('status', 'housekeeping')
            ->orderBy('room_number')
            ->get();
    }

    public function getPendingLogsProperty(): EloquentCollection
    {
        $userId = auth()->id();
        return HousekeepingLog::with(['room', 'assignedTo'])
            ->whereIn('status', ['dirty', 'in_progress'])
            ->when($this->filter === 'mine', fn ($q) => $q->where('user_id', $userId))
            ->when($this->filter === 'dirty', fn ($q) => $q->where('status', 'dirty'))
            ->when($this->filter === 'in_progress', fn ($q) => $q->where('status', 'in_progress'))
            ->latest('cleaning_timestamp')
            ->get();
    }

    public function getCompletedTodayProperty(): EloquentCollection
    {
        return HousekeepingLog::with(['room', 'assignedTo'])
            ->where('status', 'clean')
            ->whereDate('completed_at', today())
            ->latest('completed_at')
            ->take(10)
            ->get();
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Resort Operations</span>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32] flex items-center gap-2.5 mt-0.5">
                <span>Housekeeper Workspace</span>
                <span class="text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded-full font-semibold bg-[#233D32]/10 text-[#233D32]">Live Sync</span>
            </h1>
            <p class="text-xs text-[#777A72] mt-1 font-light">Real-time room sanitization, linen refresh, and readiness tracking</p>
        </div>

        {{-- Filter tabs --}}
        <div class="flex gap-1.5 bg-white p-1.5 rounded-xl border border-[#233D32]/15 text-xs font-semibold self-start md:self-auto shadow-2xs">
            <button type="button" wire:click="$set('filter', 'all')"
                    class="px-3.5 py-1.5 rounded-lg uppercase tracking-wider transition-all {{ $filter === 'all' ? 'bg-[#233D32] text-[#F7F5EF] shadow-2xs' : 'text-[#777A72] hover:text-[#233D32]' }}">
                All Tasks
            </button>
            <button type="button" wire:click="$set('filter', 'mine')"
                    class="px-3.5 py-1.5 rounded-lg uppercase tracking-wider transition-all {{ $filter === 'mine' ? 'bg-[#233D32] text-[#F7F5EF] shadow-2xs' : 'text-[#777A72] hover:text-[#233D32]' }}">
                Assigned to Me
            </button>
            <button type="button" wire:click="$set('filter', 'dirty')"
                    class="px-3.5 py-1.5 rounded-lg uppercase tracking-wider transition-all {{ $filter === 'dirty' ? 'bg-[#233D32] text-[#F7F5EF] shadow-2xs' : 'text-[#777A72] hover:text-[#233D32]' }}">
                Queued
            </button>
            <button type="button" wire:click="$set('filter', 'in_progress')"
                    class="px-3.5 py-1.5 rounded-lg uppercase tracking-wider transition-all {{ $filter === 'in_progress' ? 'bg-[#233D32] text-[#F7F5EF] shadow-2xs' : 'text-[#777A72] hover:text-[#233D32]' }}">
                In Progress
            </button>
        </div>
    </div>

    {{-- Live Flash Notification --}}
    @if($flashMessage)
        <div class="mb-6 p-4 bg-[#FAF8F4] border border-[#233D32]/20 text-[#233D32] rounded-2xl text-xs font-semibold flex items-center justify-between shadow-2xs">
            <span>✓ {{ $flashMessage }}</span>
            <button type="button" wire:click="$set('flashMessage', null)" class="text-[#777A72] hover:text-[#233D32] text-xs font-bold">Dismiss</button>
        </div>
    @endif

    {{-- Session alerts from standard redirects --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-[#FAF8F4] border border-[#233D32]/20 text-[#233D32] rounded-2xl text-xs font-semibold shadow-2xs">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Rooms Queued for Housekeeping --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden mb-8">
        <div class="px-6 py-4.5 border-b border-[#233D32]/10 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Priority Inspection</span>
                <h2 class="font-serif font-bold text-[#233D32] text-lg">Rooms Queued for Housekeeping</h2>
            </div>
            <span class="px-3 py-1 bg-[#FAF8F4] text-[#A68B61] border border-[#A68B61]/30 text-xs font-semibold rounded-xl">
                {{ $this->roomsPendingCleaning->count() }} unit(s)
            </span>
        </div>

        @if($this->roomsPendingCleaning->isEmpty())
            <div class="px-6 py-14 text-center text-[#777A72]">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3 text-[#233D32]">
                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="font-serif font-bold text-base text-[#233D32]">All Rooms Clean & Available</h3>
                <p class="text-xs text-[#777A72] mt-1 font-light">No rooms are currently flagged for inspection or sanitization.</p>
            </div>
        @else
            <div class="divide-y divide-[#233D32]/5">
                @foreach($this->roomsPendingCleaning as $room)
                    @php
                        $latestLog = $room->housekeepingLogs->first();
                        $isAssignedToMe = $latestLog && $latestLog->user_id === auth()->id();
                    @endphp
                    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $isAssignedToMe ? 'bg-[#FAF8F4]/80' : '' }}">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#FAF8F4] border border-[#233D32]/15 flex flex-col items-center justify-center text-[#233D32] font-mono font-bold text-xs shrink-0 shadow-2xs">
                                <span class="text-[9px] uppercase tracking-wider text-[#A68B61] leading-none">Unit</span>
                                <span class="text-sm font-bold leading-tight mt-0.5">{{ $room->room_number }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-serif font-bold text-base text-[#233D32]">{{ $room->name }}</p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-amber-50 text-amber-900 border border-amber-200">
                                        Housekeeping
                                    </span>
                                    @if($isAssignedToMe)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[#233D32]/10 text-[#233D32] border border-[#233D32]/20">
                                            Assigned to You
                                        </span>
                                    @elseif($latestLog && $latestLog->staff_name)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#FAF8F4] text-[#777A72] border border-[#233D32]/10">
                                            {{ $latestLog->staff_name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#777A72] mt-1 font-light">
                                    @if($latestLog)
                                        <span class="text-[#292D29] font-medium">Status:</span>
                                        <span class="font-semibold uppercase tracking-wider text-[11px] {{ $latestLog->status === 'in_progress' ? 'text-amber-700' : 'text-rose-700' }}">
                                            {{ str_replace('_', ' ', $latestLog->status) }}
                                        </span>
                                        · {{ $latestLog->notes ?? 'Pending cleaning' }}
                                        <span class="text-[#777A72]/70">({{ $latestLog->cleaning_timestamp?->diffForHumans() ?? 'recently' }})</span>
                                    @else
                                        <span class="text-[#777A72]/70 italic">Flagged for housekeeping</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
                            @if($latestLog && $latestLog->status === 'dirty')
                                <button type="button"
                                        wire:click="startCleaning({{ $latestLog->id }})"
                                        class="px-4 py-2 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 font-semibold rounded-xl text-xs uppercase tracking-wider transition-all shadow-2xs">
                                    ▶ Start Cleaning
                                </button>
                                <form method="POST" action="{{ route('housekeeper.logs.start', $latestLog) }}" class="hidden">
                                    @csrf
                                </form>
                            @endif

                            @if($latestLog && in_array($latestLog->status, ['dirty', 'in_progress']))
                                <button type="button"
                                        wire:click="openConfirmModal({{ $latestLog->id }})"
                                        class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-xs flex items-center gap-1.5">
                                    <span>✓</span>
                                    <span>Clear & Mark Available</span>
                                </button>
                                <form method="POST" action="{{ route('housekeeper.logs.confirm', $latestLog) }}" class="hidden">
                                    @csrf
                                </form>
                            @else
                                <button type="button"
                                        wire:click="confirmRoomClean({{ $room->id }})"
                                        class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-xs">
                                    ✓ Mark Clean & Available
                                </button>
                                <form method="POST" action="{{ route('housekeeper.rooms.confirm-clean', $room) }}" class="hidden">
                                    @csrf
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Active Tasks & Logs --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden mb-8">
        <div class="px-6 py-4.5 border-b border-[#233D32]/10 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Duty Register</span>
                <h2 class="font-serif font-bold text-[#233D32] text-lg">Active Cleaning Tasks & Logs</h2>
            </div>
            <span class="text-xs text-[#777A72]">{{ $this->pendingLogs->count() }} active task(s)</span>
        </div>

        @if($this->pendingLogs->isEmpty())
            <div class="px-6 py-10 text-center text-[#777A72] text-xs font-light">
                No active housekeeping tasks logged for this filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Room</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Assigned Staff</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Status</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Instructions / Notes</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Queued At</th>
                            <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#233D32]/5">
                        @foreach($this->pendingLogs as $log)
                            <tr class="hover:bg-[#FAF8F4]/50 transition-colors {{ $log->user_id === auth()->id() ? 'bg-[#FAF8F4]/40' : '' }}">
                                <td class="px-4 py-3.5 font-bold font-mono text-[#233D32]">
                                    Room {{ $log->room?->room_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($log->user_id === auth()->id())
                                        <span class="px-2 py-0.5 bg-[#233D32]/10 text-[#233D32] font-semibold rounded text-[10px] uppercase tracking-wider">You</span>
                                    @else
                                        <span class="text-[#292D29] font-medium">{{ $log->staff_name ?? $log->assignedTo?->name ?? 'Unassigned' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $log->status === 'in_progress' ? 'bg-amber-50 text-amber-900 border-amber-200' : 'bg-rose-50 text-rose-900 border-rose-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-[#595D58] max-w-xs truncate" title="{{ $log->notes }}">
                                    {{ $log->notes ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-[#777A72]">
                                    {{ $log->cleaning_timestamp?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($log->status === 'dirty')
                                            <button type="button"
                                                    wire:click="startCleaning({{ $log->id }})"
                                                    class="px-2.5 py-1 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 font-semibold rounded-lg text-[11px] transition-colors">
                                                ▶ Start
                                            </button>
                                        @endif
                                        <button type="button"
                                                wire:click="openConfirmModal({{ $log->id }})"
                                                class="px-3 py-1 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-lg text-[11px] uppercase tracking-wider transition-all shadow-2xs">
                                            ✓ Confirm Clean
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Confirmation Modal --}}
    @if($activeLogIdForNote)
        <div class="fixed inset-0 z-50 bg-[#172A22]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-[0_12px_40px_rgba(23,42,34,0.2)] border border-[#233D32]/10 space-y-4">
                <div class="flex justify-between items-center border-b border-[#233D32]/10 pb-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Verification</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Confirm Room Ready</h3>
                    </div>
                    <button type="button" wire:click="closeConfirmModal" class="text-[#777A72] hover:text-[#233D32] text-xl">&times;</button>
                </div>
                <p class="text-xs text-[#777A72] font-light leading-relaxed">
                    Confirming will mark this accommodation unit as <strong>Available</strong>, immediately allowing new resort reservations.
                </p>
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Housekeeper Notes (Optional)</label>
                        <textarea wire:model="housekeeperNote" rows="2" placeholder="e.g. Linens replaced, bathroom disinfected, room inspected."
                                  class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="closeConfirmModal"
                                class="px-4 py-2.5 border border-[#233D32]/15 hover:bg-[#FAF8F4] text-[#777A72] font-semibold rounded-xl text-xs uppercase tracking-wider transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="confirmClean({{ $activeLogIdForNote }})"
                                class="px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-sm">
                            ✓ Confirm Ready
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Recently Cleared Today --}}
    @if($this->completedToday->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-[#233D32]/10">
                <h2 class="font-serif font-bold text-[#233D32] text-base">Completed Rooms Today ({{ $this->completedToday->count() }})</h2>
            </div>
            <div class="divide-y divide-[#233D32]/5">
                @foreach($this->completedToday as $cleanLog)
                    <div class="px-6 py-3.5 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-[#233D32] font-mono">Room {{ $cleanLog->room?->room_number }}</span>
                            <span class="text-[#777A72] mx-1">·</span>
                            <span class="text-[#595D58] font-light">{{ $cleanLog->notes }}</span>
                        </div>
                        <div class="text-[#777A72] text-[11px]">
                            Cleared {{ $cleanLog->completed_at?->diffForHumans() }} by {{ $cleanLog->staff_name }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Log out link --}}
    <div class="text-center pt-2">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs uppercase tracking-wider text-[#777A72] hover:text-[#233D32] font-semibold transition-colors">
                Sign Out
            </button>
        </form>
    </div>
</div>