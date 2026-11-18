<x-layouts.admin>
    <x-slot:heading>Housekeeping & Maintenance Log</x-slot:heading>
    <x-slot:subheading>Room cleaning inspection records, sanitization timestamps, and service audit trail</x-slot:subheading>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Quick Log Entry Form --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Record Entry</span>
            <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">
                Log Housekeeping Action
            </h3>

            <form method="POST" action="{{ route('admin.housekeeping.store') }}" class="space-y-4 pt-1">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Accommodation Unit *</label>
                    <select name="room_id" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <option value="">Select Room</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->name }}) - [{{ ucfirst($r->status) }}]</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Staff Name *</label>
                    <input type="text" name="staff_name" required value="{{ auth()->user()->name }}"
                           placeholder="Housekeeper / Staff name"
                           class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Cleaning Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <option value="clean">Clean (Ready for Guest)</option>
                        <option value="in_progress">In Progress</option>
                        <option value="dirty">Dirty (Needs Cleaning)</option>
                        <option value="inspected">Inspected (Supervisor Checked)</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Timestamp</label>
                    <input type="datetime-local" name="cleaning_timestamp" value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Notes / Observations</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Linens replaced, mini-bar restocked..."
                              class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-xs">
                    Save Housekeeping Log
                </button>
            </form>
        </div>

        {{-- Filters & Audit Trail Table --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl p-4 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('admin.housekeeping.index') }}" class="flex flex-wrap items-center gap-2.5 w-full">
                    <select name="room_id" class="px-3.5 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29]">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>Room {{ $r->room_number }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="px-3.5 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29]">
                        <option value="">All Statuses</option>
                        <option value="clean" @selected(request('status') === 'clean')>Clean</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="dirty" @selected(request('status') === 'dirty')>Dirty</option>
                        <option value="inspected" @selected(request('status') === 'inspected')>Inspected</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}" class="px-3.5 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29]">

                    <button type="submit" class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] rounded-xl text-xs font-semibold uppercase tracking-wider transition-colors shadow-2xs">
                        Filter
                    </button>
                    @if(request()->hasAny(['room_id', 'status', 'date']))
                        <a href="{{ route('admin.housekeeping.index') }}" class="px-3 py-2 text-[#777A72] hover:text-[#233D32] text-xs font-medium">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Audit Trail Table --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-[#233D32]/10 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Verification</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-base">Housekeeping Audit Trail</h3>
                    </div>
                    <span class="text-xs text-[#777A72]">{{ $logs->total() }} total entries</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                            <tr>
                                <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Room</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Staff</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Status</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Timestamp</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Notes</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#233D32]/5">
                            @forelse($logs as $log)
                                <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                                    <td class="px-4 py-3.5 font-bold font-mono text-[#233D32]">
                                        Room {{ $log->room?->room_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-medium text-[#292D29]">{{ $log->staff_name ?? $log->assignedTo?->name ?? 'Staff' }}</div>
                                        @if($log->assignedTo && $log->assignedTo->name !== $log->staff_name)
                                            <div class="text-[10px] text-[#777A72]">Logged by {{ $log->assignedTo->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $badges = [
                                                'clean' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                                'in_progress' => 'bg-amber-50 text-amber-900 border-amber-200',
                                                'dirty' => 'bg-rose-50 text-rose-900 border-rose-200',
                                                'inspected' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $badges[$log->status] ?? 'bg-stone-100 text-stone-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-[#595D58]">
                                        {{ ($log->cleaning_timestamp ?? $log->created_at)?->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-[#777A72] max-w-xs truncate font-light" title="{{ $log->notes }}">
                                        {{ $log->notes ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <form method="POST" action="{{ route('admin.housekeeping.destroy', $log) }}"
                                              onsubmit="return confirm('Delete this log entry?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-700 hover:text-rose-900 font-medium">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-[#777A72] text-xs font-light">
                                        No housekeeping logs recorded matching current criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-[#233D32]/10 bg-[#FAF8F4]">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>