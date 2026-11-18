<x-layouts.admin>
    <x-slot:heading>Guest Folio & Booking Register</x-slot:heading>
    <x-slot:subheading>Comprehensive master log of all current, upcoming, and past reservations</x-slot:subheading>

    {{-- Filters & Actions Bar --}}
    <form method="GET" action="{{ route('admin.bookings.index') }}"
          class="flex flex-wrap items-center gap-3 mb-6 p-4 bg-white rounded-2xl border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)]">
        <div class="relative flex-1 min-w-56">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code, guest name or email..."
                   class="w-full pl-9 pr-4 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
            <svg class="w-4 h-4 text-[#777A72] absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <select name="status" class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
            <option value="">All Statuses</option>
            @foreach(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>

        <input type="date" name="date" value="{{ request('date') }}"
               class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">

        <button type="submit" class="px-4 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-wider rounded-xl transition-all shadow-2xs">
            Filter
        </button>

        @if(request()->hasAny(['search', 'status', 'date']))
            <a href="{{ route('admin.bookings.index') }}" class="px-3.5 py-2.5 border border-[#233D32]/15 hover:bg-[#FAF8F4] text-[#777A72] text-xs font-medium rounded-xl transition-colors">
                Clear
            </a>
        @endif

        <a href="{{ route('admin.bookings.create') }}" class="ml-auto px-4 py-2.5 bg-[#A68B61] hover:bg-[#8C734D] text-[#172A22] text-xs font-semibold uppercase tracking-widest rounded-xl transition-all shadow-xs">
            + New Walk-In
        </a>
    </form>

    {{-- Register Table --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                    <tr>
                        @foreach(['Folio Code', 'Guest Name', 'Accommodation', 'Check-In', 'Check-Out', 'Party Size', 'Total Charges', 'Folio Status', 'Settlement', 'Actions'] as $header)
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#233D32]/5">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-[#FAF8F4]/60 transition-colors">
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="font-mono text-xs text-[#A68B61] font-bold hover:underline">
                                    {{ $booking->booking_code }}
                                </a>
                                @if($booking->is_walk_in)
                                    <span class="ml-1 px-1.5 py-0.5 bg-[#FAF8F4] text-[#78866B] border border-[#233D32]/10 text-[9px] uppercase tracking-wider rounded font-bold">Walk-in</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-serif font-bold text-sm text-[#233D32]">{{ $booking->guest_name }}</div>
                                @if($booking->guest_email)
                                    <div class="text-[11px] text-[#777A72] font-light">{{ $booking->guest_email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-[#292D29]">Unit #{{ $booking->room?->room_number }}</div>
                                <div class="text-[11px] text-[#777A72]">{{ $booking->room?->category?->name }}</div>
                            </td>
                            <td class="px-4 py-4 text-[#595D58] font-medium">{{ $booking->check_in->format('M d, Y') }}</td>
                            <td class="px-4 py-4 text-[#595D58] font-medium">{{ $booking->check_out->format('M d, Y') }}</td>
                            <td class="px-4 py-4 text-[#777A72]">
                                {{ $booking->adults }}A
                                @if($booking->children > 0) {{ $booking->children }}C @endif
                                @if($booking->pets > 0) <span class="text-[#A68B61]">🐾{{ $booking->pets }}</span> @endif
                            </td>
                            <td class="px-4 py-4 font-serif font-bold text-sm text-[#233D32]">₱{{ number_format($booking->total_price, 2) }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'confirmed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                        'checked_out' => 'bg-stone-100 text-stone-700 border-stone-200',
                                        'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        'no_show' => 'bg-stone-200 text-stone-600 border-stone-300',
                                    ];
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $statusClasses[$booking->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $booking->payment_status === 'paid' ? 'bg-[#A68B61]/15 text-[#172A22] border-[#A68B61]/30' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                       class="px-2.5 py-1 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-[11px] font-semibold rounded-lg transition-colors">
                                        Folio
                                    </a>
                                    @if($booking->status === 'confirmed')
                                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-[11px] font-semibold rounded-lg transition-colors">
                                                Check In
                                            </button>
                                        </form>
                                    @endif
                                    @if($booking->status === 'checked_in')
                                        <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-[#A68B61] hover:bg-[#8C734D] text-[#172A22] text-[11px] font-semibold rounded-lg transition-colors">
                                                Check Out
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16 text-center text-[#777A72]">
                                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-serif font-bold text-base text-[#233D32]">No Bookings Found</h3>
                                <p class="text-xs text-[#777A72] mt-1 font-light">Try adjusting your filters or register a new walk-in guest.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-[#233D32]/10 bg-[#FAF8F4]">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>