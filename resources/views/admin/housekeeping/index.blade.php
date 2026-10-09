<x-layouts.admin>
    <x-slot:heading>Housekeeping Logs</x-slot:heading>
    <x-slot:subheading>Room cleaning logs and maintenance audit trail</x-slot:subheading>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Quick Log Entry Form --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
            <h3 class="font-bold text-stone-900 mb-4 flex items-center gap-2">
                <span>🧹</span> Record Housekeeping Log
            </h3>

            <form method="POST" action="{{ route('admin.housekeeping.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Room</label>
                    <select name="room_id" required class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select Room</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->name }}) - [{{ ucfirst($r->status) }}]</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Staff Name</label>
                    <input type="text" name="staff_name" required value="{{ auth()->user()->name }}"
                           placeholder="Housekeeper / Staff name"
                           class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Cleaning Status</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="clean">Clean (Ready for Guest)</option>
                        <option value="in_progress">In Progress</option>
                        <option value="dirty">Dirty (Needs Cleaning)</option>
                        <option value="inspected">Inspected (Supervisor Checked)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Timestamp</label>
                    <input type="datetime-local" name="cleaning_timestamp" value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Notes / Observations</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Linens replaced, mini-bar restocked..."
                              class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Save Log Entry
                </button>
            </form>
        </div>

        {{-- Filters & Audit Trail Table --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('admin.housekeeping.index') }}" class="flex flex-wrap items-center gap-3 w-full">
                    <select name="room_id" class="px-3 py-2 border border-stone-200 rounded-xl text-xs">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>Room {{ $r->room_number }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="px-3 py-2 border border-stone-200 rounded-xl text-xs">
                        <option value="">All Statuses</option>
                        <option value="clean" @selected(request('status') === 'clean')>Clean</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="dirty" @selected(request('status') === 'dirty')>Dirty</option>
                        <option value="inspected" @selected(request('status') === 'inspected')>Inspected</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2 border border-stone-200 rounded-xl text-xs">

                    <button type="submit" class="px-4 py-2 bg-stone-800 text-white rounded-xl text-xs font-semibold hover:bg-stone-900 transition-colors">
                        Filter
                    </button>
                    @if(request()->hasAny(['room_id', 'status', 'date']))
                        <a href="{{ route('admin.housekeeping.index') }}" class="px-3 py-2 text-stone-500 hover:text-stone-800 text-xs">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Audit Trail Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <h3 class="font-bold text-stone-900 text-sm">Housekeeping Audit Trail</h3>
                    <span class="text-xs text-stone-400">{{ $logs->total() }} total entries</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Room</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Staff</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Timestamp</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Notes</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wide">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            @forelse($logs as $log)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-4 py-3.5 font-bold font-mono text-stone-900">
                                        Room {{ $log->room?->room_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-medium text-stone-900">{{ $log->staff_name ?? $log->assignedTo?->name ?? 'Staff' }}</div>
                                        @if($log->assignedTo && $log->assignedTo->name !== $log->staff_name)
                                            <div class="text-[11px] text-stone-400">Logged by {{ $log->assignedTo->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $badges = [
                                                'clean' => 'bg-emerald-100 text-emerald-800',
                                                'in_progress' => 'bg-amber-100 text-amber-800',
                                                'dirty' => 'bg-red-100 text-red-800',
                                                'inspected' => 'bg-blue-100 text-blue-800',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badges[$log->status] ?? 'bg-stone-100 text-stone-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600">
                                        {{ ($log->cleaning_timestamp ?? $log->created_at)?->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 max-w-xs truncate" title="{{ $log->notes }}">
                                        {{ $log->notes ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <form method="POST" action="{{ route('admin.housekeeping.destroy', $log) }}"
                                              onsubmit="return confirm('Delete this log entry?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-stone-400 text-sm">
                                        No housekeeping logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="px-4 py-3 border-t border-stone-100">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
