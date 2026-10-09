<x-layouts.app>
    <x-slot:title>Housekeeper Dashboard</x-slot:title>

    <div class="pt-20 pb-20 bg-stone-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-black text-stone-900">🧹 Housekeeper Dashboard</h1>
                <p class="text-stone-500 mt-1">Rooms pending your attention after guest checkout.</p>
            </div>

            {{-- Session alerts --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-medium">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-sm font-medium">
                    ❌ {{ session('error') }}
                </div>
            @endif

            {{-- Rooms Needing Cleaning --}}
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-stone-900">Rooms Queued for Housekeeping</h2>
                        <p class="text-xs text-stone-400 mt-0.5">Rooms that have checked out and must be inspected and cleared before new guests can occupy.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg">{{ $roomsPendingCleaning->count() }} room(s)</span>
                </div>

                @if($roomsPendingCleaning->isEmpty())
                    <div class="px-6 py-12 text-center text-stone-400 text-sm">
                        <div class="text-3xl mb-2">🎉</div>
                        <p class="font-semibold text-stone-700">All rooms are clean and available!</p>
                        <p class="text-xs text-stone-400 mt-1">No rooms are currently awaiting housekeeping.</p>
                    </div>
                @else
                    <div class="divide-y divide-stone-50">
                        @foreach($roomsPendingCleaning as $room)
                            @php
                                $latestLog = $room->housekeepingLogs->first();
                                $isAssignedToMe = $latestLog && $latestLog->user_id === auth()->id();
                            @endphp
                            <div class="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $isAssignedToMe ? 'bg-amber-50/40' : '' }}">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-100 flex flex-col items-center justify-center text-amber-800 font-black text-sm shrink-0 border border-amber-200">
                                        <span class="text-[10px] uppercase font-bold text-amber-600 leading-none">Room</span>
                                        <span class="text-base leading-tight">{{ $room->room_number }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-bold text-stone-900 text-base">{{ $room->name }}</p>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">
                                                🧹 Housekeeping
                                            </span>
                                            @if($isAssignedToMe)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                    ⭐ Assigned to You
                                                </span>
                                            @elseif($latestLog && $latestLog->staff_name)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-600">
                                                    👤 {{ $latestLog->staff_name }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-stone-500 mt-1">
                                            @if($latestLog)
                                                <span class="text-stone-700 font-medium">Status:</span>
                                                <span class="font-semibold uppercase {{ $latestLog->status === 'in_progress' ? 'text-amber-600' : 'text-rose-600' }}">
                                                    {{ str_replace('_', ' ', $latestLog->status) }}
                                                </span>
                                                · {{ $latestLog->notes ?? 'Pending cleaning' }}
                                                <span class="text-stone-400">({{ $latestLog->cleaning_timestamp?->diffForHumans() ?? 'recently' }})</span>
                                            @else
                                                <span class="text-stone-400 italic">Flagged for housekeeping (no pending log)</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
                                    @if($latestLog && $latestLog->status === 'dirty')
                                        <form method="POST" action="{{ route('housekeeper.logs.start', $latestLog) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-semibold rounded-xl text-xs transition-colors border border-stone-200">
                                                ▶ Start Cleaning
                                            </button>
                                        </form>
                                    @endif

                                    @if($latestLog && in_array($latestLog->status, ['dirty', 'in_progress']))
                                        <div x-data="{ openNote: false }">
                                            <button @click="openNote = !openNote"
                                                    type="button"
                                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm flex items-center gap-1.5">
                                                <span>✅</span>
                                                <span>Clear & Mark Available</span>
                                            </button>

                                            {{-- Inline modal / prompt for notes --}}
                                            <div x-show="openNote" x-cloak
                                                 class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
                                                <div @click.away="openNote = false"
                                                     class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-stone-100 space-y-4">
                                                    <div class="flex justify-between items-center">
                                                        <h3 class="font-bold text-stone-900 text-base">Confirm Room {{ $room->room_number }} is Clean</h3>
                                                        <button @click="openNote = false" class="text-stone-400 hover:text-stone-600">&times;</button>
                                                    </div>
                                                    <p class="text-xs text-stone-500">
                                                        Confirming will mark this room as <strong>Available</strong>, allowing new guests to book and occupy it.
                                                    </p>
                                                    <form method="POST" action="{{ route('housekeeper.logs.confirm', $latestLog) }}" class="space-y-3">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-xs font-semibold text-stone-700 mb-1">Housekeeper Notes (Optional)</label>
                                                            <textarea name="notes" rows="2" placeholder="e.g. Linens replaced, bathroom disinfected, room inspected."
                                                                      class="w-full px-3 py-2 border border-stone-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                                                        </div>
                                                        <div class="flex justify-end gap-2 pt-2">
                                                            <button type="button" @click="openNote = false"
                                                                    class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-xl text-xs">
                                                                Cancel
                                                            </button>
                                                            <button type="submit"
                                                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs">
                                                                ✓ Confirm Ready for Occupancy
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Fallback direct clearance --}}
                                        <form method="POST" action="{{ route('housekeeper.rooms.confirm-clean', $room) }}"
                                              onsubmit="return confirm('Mark Room {{ $room->room_number }} clean and available?')">
                                            @csrf
                                            <button type="submit"
                                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                                                ✅ Mark Clean & Available
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Open Housekeeping Logs (dirty / in_progress) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-stone-900">All Active Tasks & Logs</h2>
                        <p class="text-xs text-stone-400 mt-0.5">Tasks currently awaiting inspection or being cleaned.</p>
                    </div>
                    <span class="text-xs font-semibold text-stone-400">{{ $pendingLogs->count() }} active</span>
                </div>

                @if($pendingLogs->isEmpty())
                    <div class="px-6 py-8 text-center text-stone-400 text-sm">
                        No active housekeeping tasks logged. ✨
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-stone-50 border-b border-stone-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Room</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Assigned Staff</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Instructions / Notes</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Queued At</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-50">
                                @foreach($pendingLogs as $log)
                                    <tr class="hover:bg-stone-50/50 transition-colors {{ $log->user_id === auth()->id() ? 'bg-amber-50/20' : '' }}">
                                        <td class="px-4 py-3 font-bold font-mono text-stone-900">
                                            Room {{ $log->room?->room_number ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            @if($log->user_id === auth()->id())
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">You</span>
                                            @else
                                                <span class="text-stone-600 font-medium">{{ $log->staff_name ?? $log->assignedTo?->name ?? 'Unassigned' }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                                {{ $log->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-stone-600 max-w-xs truncate" title="{{ $log->notes }}">
                                            {{ $log->notes ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-stone-500">
                                            {{ $log->cleaning_timestamp?->diffForHumans() ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($log->status === 'dirty')
                                                    <form method="POST" action="{{ route('housekeeper.logs.start', $log) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium rounded-lg text-xs transition-colors">
                                                            ▶ Start
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('housekeeper.logs.confirm', $log) }}"
                                                      onsubmit="return confirm('Confirm Room {{ $log->room?->room_number }} is clean and ready?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition-colors">
                                                        ✅ Confirm Clean
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Recently Cleared Today --}}
            @if(isset($completedToday) && $completedToday->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-stone-100">
                        <h2 class="font-bold text-stone-900">Cleared Rooms Today ({{ $completedToday->count() }})</h2>
                    </div>
                    <div class="divide-y divide-stone-50">
                        @foreach($completedToday as $cleanLog)
                            <div class="px-6 py-3 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-stone-900">Room {{ $cleanLog->room?->room_number }}</span>
                                    <span class="text-stone-400 mx-1">·</span>
                                    <span class="text-stone-500">{{ $cleanLog->notes }}</span>
                                </div>
                                <div class="text-stone-400">
                                    Cleared {{ $cleanLog->completed_at?->diffForHumans() }} by {{ $cleanLog->staff_name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Log out link --}}
            <div class="text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-stone-400 hover:text-stone-700 font-medium transition-colors">
                        Sign Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-layouts.app>
