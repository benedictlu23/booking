<x-layouts.admin>
    <x-slot:heading>Executive Operations Dashboard</x-slot:heading>
    <x-slot:subheading>Agro-Inland Resort daily performance, guest movement, and occupancy metrics</x-slot:subheading>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        {{-- Stat 1: Revenue --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Financial Metric</span>
                <span class="text-xs text-[#777A72]">Current Month</span>
            </div>
            <p class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32]">₱{{ number_format($totalRevenue, 2) }}</p>
            <p class="text-xs text-[#777A72] mt-1 font-light">Gross Monthly Revenue</p>
        </div>

        {{-- Stat 2: Bookings --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Reservation Volume</span>
                <span class="text-xs text-[#777A72]">This Month</span>
            </div>
            <p class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32]">{{ $totalBookings }}</p>
            <p class="text-xs text-[#777A72] mt-1 font-light">Total Confirmed & Completed</p>
        </div>

        {{-- Stat 3: Occupancy --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Resort Capacity</span>
                <span class="text-xs text-[#777A72]">{{ $occupiedRooms }} of {{ $totalRooms }} units</span>
            </div>
            <p class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32]">{{ $occupancyRate }}%</p>
            <p class="text-xs text-[#777A72] mt-1 font-light">Live Occupancy Rate</p>
        </div>

        {{-- Stat 4: Pending Bookings --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Action Required</span>
                <span class="text-xs font-semibold {{ $pendingBookings->count() > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $pendingBookings->count() > 0 ? 'Awaiting Review' : 'All Clear' }}
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32]">{{ $pendingBookings->count() }}</p>
            <p class="text-xs text-[#777A72] mt-1 font-light">Pending Guest Reservations</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Main Activity Column --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Revenue Chart --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-6 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Performance Trend</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Revenue Trajectory</h3>
                    </div>
                    <span class="text-xs text-[#777A72] font-light">Past 6 Calendar Months</span>
                </div>
                @php
                    $maxRev = $revenueChartData->max('revenue') ?: 1;
                @endphp
                <div class="flex items-end gap-3 sm:gap-6 h-44 pt-4 px-2">
                    @foreach($revenueChartData as $item)
                        <div class="flex-1 flex flex-col items-center gap-2.5 h-full justify-end group">
                            <span class="text-[10px] font-mono text-[#777A72] opacity-0 group-hover:opacity-100 transition-opacity">
                                ₱{{ number_format($item['revenue'] / 1000, 1) }}k
                            </span>
                            <div class="w-full max-w-[48px] bg-[#233D32] hover:bg-[#A68B61] rounded-t-lg transition-all duration-300 shadow-xs"
                                 style="height: {{ max(6, ($item['revenue'] / $maxRev) * 100) }}%"
                                 title="₱{{ number_format($item['revenue'], 2) }}"></div>
                            <span class="text-[11px] font-medium text-[#777A72] uppercase tracking-wider whitespace-nowrap">{{ $item['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Today's Check-ins --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Arrivals</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Today's Check-Ins</h3>
                    </div>
                    <span class="px-3 py-1 bg-[#233D32]/10 text-[#233D32] text-xs font-semibold rounded-lg">
                        {{ $todaysCheckIns->count() }} Expected
                    </span>
                </div>
                @forelse($todaysCheckIns as $booking)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 py-3.5 border-b border-[#233D32]/5 last:border-0">
                        <div>
                            <p class="font-serif font-bold text-[#233D32] text-sm">{{ $booking->guest_name }}</p>
                            <p class="text-xs text-[#777A72] font-mono">Unit {{ $booking->room?->room_number }} · {{ $booking->booking_code }}</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 text-[11px] uppercase tracking-wider rounded-lg font-semibold border
                                {{ $booking->status === 'confirmed' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                            <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs uppercase tracking-wider font-semibold rounded-lg transition-all shadow-2xs">
                                    Check In
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#777A72] text-center py-6 font-light">No guest check-ins scheduled for today.</p>
                @endforelse
            </div>

            {{-- Recent Bookings Table --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Master Register</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Recent Folio Bookings</h3>
                    </div>
                    <a href="{{ route('admin.bookings.index') }}" class="text-xs uppercase tracking-wider font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors">
                        View Complete Register →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-[#777A72] border-b border-[#233D32]/10">
                                <th class="pb-3 font-semibold">Folio Code</th>
                                <th class="pb-3 font-semibold">Guest</th>
                                <th class="pb-3 font-semibold">Unit</th>
                                <th class="pb-3 font-semibold">Check-In</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#233D32]/5">
                            @foreach($recentBookings as $booking)
                                <tr class="hover:bg-[#FAF8F4] transition-colors">
                                    <td class="py-3 font-mono">
                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                           class="text-[#A68B61] font-bold hover:underline">
                                            {{ $booking->booking_code }}
                                        </a>
                                    </td>
                                    <td class="py-3 font-medium text-[#292D29]">{{ $booking->guest_name }}</td>
                                    <td class="py-3 text-[#777A72]">#{{ $booking->room?->room_number }}</td>
                                    <td class="py-3 text-[#777A72]">{{ $booking->check_in->format('M d, Y') }}</td>
                                    <td class="py-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                'confirmed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                                'checked_out' => 'bg-stone-100 text-stone-700 border-stone-200',
                                                'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $statusColors[$booking->status] ?? 'bg-stone-100 text-stone-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-serif font-bold text-right text-[#233D32]">₱{{ number_format($booking->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Operational Right Column --}}
        <div class="space-y-6">

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Front Desk</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4">Quick Operations</h3>
                <div class="space-y-2.5">
                    <a href="{{ route('admin.bookings.create') }}"
                       class="flex items-center justify-between p-3.5 bg-[#FAF8F4] hover:bg-[#EFEBE1] rounded-xl border border-[#233D32]/10 transition-colors group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#233D32] text-[#F7F5EF] flex items-center justify-center font-bold text-sm">+</span>
                            <span class="text-xs font-semibold text-[#233D32] uppercase tracking-wider">Walk-In Reservation</span>
                        </div>
                        <span class="text-[#A68B61] group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                    <a href="{{ route('admin.bookings.matrix') }}"
                       class="flex items-center justify-between p-3.5 bg-[#FAF8F4] hover:bg-[#EFEBE1] rounded-xl border border-[#233D32]/10 transition-colors group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#FAF8F4] border border-[#233D32]/15 text-[#233D32] flex items-center justify-center font-bold text-sm">📅</span>
                            <span class="text-xs font-semibold text-[#233D32] uppercase tracking-wider">Reservation Matrix</span>
                        </div>
                        <span class="text-[#A68B61] group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                    <a href="{{ route('admin.rooms.index') }}"
                       class="flex items-center justify-between p-3.5 bg-[#FAF8F4] hover:bg-[#EFEBE1] rounded-xl border border-[#233D32]/10 transition-colors group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#FAF8F4] border border-[#233D32]/15 text-[#233D32] flex items-center justify-center font-bold text-sm">🏡</span>
                            <span class="text-xs font-semibold text-[#233D32] uppercase tracking-wider">Room Inventory</span>
                        </div>
                        <span class="text-[#A68B61] group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>

            {{-- Today's Check-Outs --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Departures</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Today's Check-Outs</h3>
                    </div>
                    <span class="px-2.5 py-1 bg-[#FAF8F4] text-[#A68B61] border border-[#A68B61]/30 text-xs font-semibold rounded-lg">
                        {{ $todaysCheckOuts->count() }} Total
                    </span>
                </div>
                @forelse($todaysCheckOuts as $booking)
                    <div class="flex items-center justify-between py-3 border-b border-[#233D32]/5 last:border-0">
                        <div>
                            <p class="font-serif font-bold text-[#233D32] text-sm">{{ $booking->guest_name }}</p>
                            <p class="text-xs text-[#777A72] font-mono">Unit {{ $booking->room?->room_number }} · {{ $booking->booking_code }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($booking->status === 'checked_out')
                                <span class="px-2.5 py-1 bg-stone-100 text-stone-700 rounded-lg text-[10px] uppercase tracking-wider font-semibold">
                                    Completed
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-[#A68B61] hover:bg-[#8C734D] text-[#172A22] text-xs font-semibold uppercase tracking-wider rounded-lg transition-all shadow-2xs">
                                        Check Out
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#777A72] text-center py-6 font-light">No guest departures scheduled for today.</p>
                @endforelse
            </div>

            {{-- Top Frequent Guests --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Loyalty</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Top Frequent Guests</h3>
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="text-xs uppercase tracking-wider font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors">Directory →</a>
                </div>
                <div class="space-y-2.5">
                    @foreach($frequentGuests as $index => $guest)
                        <a href="{{ route('admin.customers.show', $guest) }}" class="flex items-center gap-3 p-2 hover:bg-[#FAF8F4] rounded-xl border border-transparent hover:border-[#233D32]/10 transition-all">
                            <span class="w-5 text-center font-serif font-bold text-[#A68B61] text-xs">{{ $index + 1 }}</span>
                            @if($guest->avatar)
                                <img src="{{ $guest->avatar }}" alt="{{ $guest->name }}" class="w-8 h-8 rounded-lg object-cover border border-[#233D32]/10">
                            @else
                                <div class="w-8 h-8 bg-[#233D32] rounded-lg flex items-center justify-center text-[#F7F5EF] font-serif font-bold text-xs">
                                    {{ substr($guest->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-[#233D32] truncate">{{ $guest->name }}</p>
                                <p class="text-[11px] text-[#777A72]">₱{{ number_format($guest->bookings_sum_total_price ?? 0, 0) }} total spent</p>
                            </div>
                            <span class="px-2 py-0.5 bg-[#FAF8F4] border border-[#233D32]/10 text-[#233D32] text-[10px] uppercase tracking-wider font-semibold rounded-md shrink-0">
                                {{ $guest->bookings_count ?? $guest->visit_count }} stays
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>