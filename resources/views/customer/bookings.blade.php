<x-layouts.app>
    <x-slot:title>My Bookings</x-slot:title>

    <div class="pt-24 pb-16 min-h-screen bg-stone-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6 pt-4">
                <div>
                    <h1 class="text-2xl font-black text-stone-900">My Bookings</h1>
                    <p class="text-stone-500 text-sm mt-1">All your reservations in one place</p>
                </div>
                <a href="{{ route('rooms.search') }}"
                   class="px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    + New Booking
                </a>
            </div>

            {{-- Status Filter --}}
            <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
                @php
                    $filters = ['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'checked_in' => 'Checked In', 'checked_out' => 'Completed', 'cancelled' => 'Cancelled'];
                @endphp
                @foreach($filters as $value => $label)
                    <a href="{{ route('customer.bookings', array_filter(['status' => $value])) }}"
                       class="shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ request('status', '') === $value ? 'bg-emerald-600 text-white' : 'bg-white text-stone-600 border border-stone-200 hover:border-emerald-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="space-y-3">
                @forelse($bookings as $booking)
                    <a href="{{ route('customer.bookings.show', $booking) }}"
                       class="block bg-white rounded-2xl p-5 shadow-sm border border-stone-100 hover:shadow-md hover:border-emerald-200 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 shrink-0">
                                @if($booking->room?->photos->first())
                                    <img src="{{ asset('storage/'.$booking->room->photos->first()->path) }}"
                                         alt="{{ $booking->room->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-2xl">🏡</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs text-emerald-600 font-bold">{{ $booking->booking_code }}</span>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'confirmed' => 'bg-blue-100 text-blue-700',
                                            'checked_in' => 'bg-emerald-100 text-emerald-700',
                                            'checked_out' => 'bg-stone-100 text-stone-600',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'bg-stone-100 text-stone-600' }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </div>
                                <p class="font-bold text-stone-900 mt-1 truncate">{{ $booking->room?->name }}</p>
                                <p class="text-xs text-stone-500">
                                    {{ $booking->check_in->format('M d') }} → {{ $booking->check_out->format('M d, Y') }}
                                    · {{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-black text-stone-900">₱{{ number_format($booking->total_price, 2) }}</p>
                                <p class="text-xs text-stone-400 mt-0.5 {{ $booking->payment_status === 'paid' ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="py-20 text-center bg-white rounded-2xl border border-stone-100 shadow-sm">
                        <div class="text-5xl mb-3">🌿</div>
                        <p class="font-semibold text-stone-700">No bookings yet</p>
                        <a href="{{ route('rooms.search') }}"
                           class="mt-4 inline-flex items-center px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 text-sm transition-colors">
                            Browse Rooms
                        </a>
                    </div>
                @endforelse
            </div>

            @if($bookings->hasPages())
                <div class="mt-6">{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
