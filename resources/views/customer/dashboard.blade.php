<x-layouts.app>
    <x-slot:title>My Dashboard</x-slot:title>

    <div class="pt-24 pb-16 min-h-screen bg-stone-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Profile Header --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-8 mb-8 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg width=60 height=60 viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><g fill=&quot;white&quot;><path d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4z&quot;/></g></svg>')"></div>
                <div class="relative flex items-center gap-6">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                             class="w-20 h-20 rounded-2xl object-cover shadow-xl border-4 border-white/30">
                    @else
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-white font-black text-3xl border-4 border-white/30">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <h1 class="text-2xl font-black text-white">{{ $user->name }}</h1>
                        <p class="text-emerald-200 text-sm">{{ $user->email }}</p>
                        @if($user->last_visited_at)
                            <p class="text-emerald-300 text-xs mt-1">Last visited {{ $user->last_visited_at->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20 text-center">
                            <p class="text-3xl font-black text-white">{{ $user->visit_count }}</p>
                            <p class="text-emerald-200 text-sm font-medium">Total Visits</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Bookings --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-stone-900">Active Bookings</h2>
                    <a href="{{ route('customer.bookings') }}" class="text-sm text-emerald-600 font-medium hover:underline">View all →</a>
                </div>

                @forelse($activeBookings as $booking)
                    <a href="{{ route('customer.bookings.show', $booking) }}"
                       class="block bg-white rounded-2xl p-5 shadow-sm border border-stone-100 hover:shadow-md hover:border-emerald-200 transition-all mb-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @php
                                    $statusConfig = [
                                        'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => '⏳ Pending'],
                                        'confirmed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => '✅ Confirmed'],
                                        'checked_in' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => '🏨 Checked In'],
                                    ];
                                    $config = $statusConfig[$booking->status] ?? ['bg' => 'bg-stone-100', 'text' => 'text-stone-600', 'label' => ucfirst($booking->status)];
                                @endphp
                                <div class="w-12 h-12 {{ $config['bg'] }} rounded-xl flex items-center justify-center text-xl">
                                    {{ $booking->room?->category?->icon ?? '🏡' }}
                                </div>
                                <div>
                                    <p class="font-bold text-stone-900">{{ $booking->room?->name }}</p>
                                    <p class="text-sm text-stone-500">{{ $booking->booking_code }} · Room {{ $booking->room?->room_number }}</p>
                                    <p class="text-xs text-stone-400 mt-0.5">
                                        {{ $booking->check_in->format('M d') }} → {{ $booking->check_out->format('M d, Y') }}
                                        ({{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }})
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-stone-900">₱{{ number_format($booking->total_price, 2) }}</p>
                                <span class="inline-block mt-1 px-2.5 py-1 {{ $config['bg'] }} {{ $config['text'] }} text-xs font-semibold rounded-lg">
                                    {{ $config['label'] }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-stone-100">
                        <div class="text-5xl mb-3">🌿</div>
                        <p class="font-semibold text-stone-700">No active bookings</p>
                        <p class="text-sm text-stone-400 mt-1 mb-5">Ready for your next getaway?</p>
                        <a href="{{ route('rooms.search') }}"
                           class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 text-sm transition-colors">
                            Browse Rooms
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Past Bookings --}}
            @if($pastBookings->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold text-stone-900 mb-4">Past Stays</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($pastBookings as $booking)
                            <a href="{{ route('customer.bookings.show', $booking) }}"
                               class="bg-white rounded-2xl p-5 shadow-sm border border-stone-100 hover:shadow-md transition-all block">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-stone-900">{{ $booking->room?->name }}</p>
                                        <p class="text-xs text-stone-400">{{ $booking->check_in->format('M d') }} – {{ $booking->check_out->format('M d, Y') }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-stone-400 line-through">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
