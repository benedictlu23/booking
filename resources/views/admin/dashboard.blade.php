<x-layouts.admin>
    <x-slot:heading>Dashboard</x-slot:heading>
    <x-slot:subheading>Welcome back, {{ auth()->user()->name }}</x-slot:subheading>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        @php
            $stats = [
                ['label' => 'Monthly Revenue', 'value' => '₱'.number_format($totalRevenue, 2), 'icon' => '💰', 'color' => 'emerald', 'sub' => 'This month'],
                ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => '📋', 'color' => 'blue', 'sub' => 'This month'],
                ['label' => 'Occupancy Rate', 'value' => $occupancyRate.'%', 'icon' => '🏨', 'color' => 'amber', 'sub' => $occupiedRooms.' of '.$totalRooms.' rooms'],
                ['label' => 'Pending Bookings', 'value' => $pendingBookings->count(), 'icon' => '⏳', 'color' => 'rose', 'sub' => 'Requires action'],
            ];
        @endphp
        @foreach($stats as $stat)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-{{ $stat['color'] }}-50 rounded-xl flex items-center justify-center text-2xl">
                        {{ $stat['icon'] }}
                    </div>
                    <span class="text-xs font-medium text-stone-400">{{ $stat['sub'] }}</span>
                </div>
                <p class="text-3xl font-black text-stone-900">{{ $stat['value'] }}</p>
                <p class="text-sm text-stone-500 mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Today's Activity --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Revenue Chart (simple bar chart) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-stone-900">Revenue Trend</h3>
                    <span class="text-xs text-stone-400">Last 6 months</span>
                </div>
                @php
                    $maxRev = $revenueChartData->max('revenue') ?: 1;
                @endphp
                <div class="flex items-end gap-3 h-40">
                    @foreach($revenueChartData as $item)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-500 rounded-t-lg transition-all hover:bg-emerald-600"
                                 style="height: {{ max(4, ($item['revenue'] / $maxRev) * 100) }}%"
                                 title="₱{{ number_format($item['revenue'], 2) }}"></div>
                            <span class="text-xs text-stone-400 whitespace-nowrap">{{ $item['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Today's Check-ins --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-stone-900">Today's Check-Ins</h3>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg">
                        {{ $todaysCheckIns->count() }} expected
                    </span>
                </div>
                @forelse($todaysCheckIns as $booking)
                    <div class="flex items-center justify-between py-3 border-b border-stone-50 last:border-0">
                        <div>
                            <p class="font-semibold text-stone-900 text-sm">{{ $booking->guest_name }}</p>
                            <p class="text-xs text-stone-500">Room {{ $booking->room?->room_number }} · {{ $booking->booking_code }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs rounded-lg font-medium
                                {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                            <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                                    Check In
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-400 text-center py-4">No check-ins scheduled for today.</p>
                @endforelse
            </div>

            {{-- Recent Bookings --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-stone-900">Recent Bookings</h3>
                    <a href="{{ route('admin.bookings.index') }}" class="text-sm text-emerald-600 font-medium hover:underline">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-stone-400 border-b border-stone-100">
                                <th class="pb-3 font-medium">Code</th>
                                <th class="pb-3 font-medium">Guest</th>
                                <th class="pb-3 font-medium">Room</th>
                                <th class="pb-3 font-medium">Check-In</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            @foreach($recentBookings as $booking)
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="py-3">
                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                           class="font-mono text-xs text-emerald-600 font-semibold hover:underline">
                                            {{ $booking->booking_code }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-stone-700">{{ $booking->guest_name }}</td>
                                    <td class="py-3 text-stone-500">{{ $booking->room?->room_number }}</td>
                                    <td class="py-3 text-stone-500">{{ $booking->check_in->format('M d') }}</td>
                                    <td class="py-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'confirmed' => 'bg-blue-100 text-blue-700',
                                                'checked_in' => 'bg-emerald-100 text-emerald-700',
                                                'checked_out' => 'bg-stone-100 text-stone-700',
                                                'cancelled' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $statusColors[$booking->status] ?? 'bg-stone-100 text-stone-600' }}">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-semibold text-stone-900">₱{{ number_format($booking->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-5">

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.bookings.create') }}"
                       class="flex items-center gap-3 p-3 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors text-sm font-medium text-emerald-800">
                        <span class="text-xl">➕</span> New Walk-In Booking
                    </a>
                    <a href="{{ route('admin.bookings.matrix') }}"
                       class="flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors text-sm font-medium text-blue-800">
                        <span class="text-xl">📅</span> View Reservation Matrix
                    </a>
                    <a href="{{ route('admin.rooms.index') }}"
                       class="flex items-center gap-3 p-3 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors text-sm font-medium text-amber-800">
                        <span class="text-xl">🏨</span> Manage Rooms
                    </a>
                </div>
            </div>

            {{-- Today's Check-Outs --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Today's Check-Outs</h3>
                @forelse($todaysCheckOuts as $booking)
                    <div class="flex items-center justify-between py-3 border-b border-stone-50 last:border-0">
                        <div>
                            <p class="font-semibold text-stone-900 text-sm">{{ $booking->guest_name }}</p>
                            <p class="text-xs text-stone-500">Room {{ $booking->room?->room_number }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-amber-500 text-white text-xs font-semibold rounded-lg hover:bg-amber-600 transition-colors">
                                Check Out
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-stone-400 text-center py-4">No check-outs today.</p>
                @endforelse
            </div>

            {{-- Frequent Guests --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Top Guests 🏆</h3>
                @foreach($frequentGuests as $index => $guest)
                    <div class="flex items-center gap-3 py-2.5 border-b border-stone-50 last:border-0">
                        <span class="w-6 text-center font-black text-stone-400 text-sm">{{ $index + 1 }}</span>
                        @if($guest->avatar)
                            <img src="{{ $guest->avatar }}" alt="{{ $guest->name }}" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                {{ substr($guest->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-stone-900 truncate">{{ $guest->name }}</p>
                        </div>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg">
                            {{ $guest->visit_count }}x
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
