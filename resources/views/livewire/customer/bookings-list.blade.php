<?php

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Volt\Component;

new class extends Component {
    public string $statusFilter = '';
    public string $search = '';

    public function mount(?string $initialStatus = null): void
    {
        $this->statusFilter = $initialStatus ?: request('status', '');
    }

    public function getBookingsProperty(): EloquentCollection
    {
        return Booking::with(['room.photos', 'room.category'])
            ->where('user_id', auth()->id())
            ->when(filled($this->statusFilter), fn ($q) => $q->where('status', $this->statusFilter))
            ->when(filled($this->search), function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('booking_code', 'like', $term)
                        ->orWhereHas('room', fn ($r) => $r->where('name', 'like', $term)->orWhere('room_number', 'like', $term));
                });
            })
            ->latest()
            ->get();
    }
}; ?>

<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pt-4">
        <div>
            <h1 class="text-2xl font-black text-stone-900 flex items-center gap-2">
                My Bookings
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800">Live</span>
            </h1>
            <p class="text-stone-500 text-sm mt-1">All your reservations in one place</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search booking code..."
                   class="px-3.5 py-2 bg-white border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm">
            <a href="{{ route('rooms.search') }}"
               class="px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors whitespace-nowrap shadow-sm">
                + New Booking
            </a>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
        @php
            $filters = [
                '' => 'All',
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'checked_in' => 'Checked In',
                'checked_out' => 'Completed',
                'cancelled' => 'Cancelled'
            ];
        @endphp
        @foreach($filters as $value => $label)
            <button type="button"
                    wire:click="$set('statusFilter', '{{ $value }}')"
                    class="shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $statusFilter === $value ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-stone-600 border border-stone-200 hover:border-emerald-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse($this->bookings as $booking)
            <a href="{{ route('customer.bookings.show', $booking) }}"
               class="block bg-white rounded-2xl p-5 shadow-sm border border-stone-100 hover:shadow-md hover:border-emerald-200 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 shrink-0">
                        @if($booking->room?->photos->first())
                            <img src="{{ $booking->room->photos->first()->path }}"
                                 alt="{{ $booking->room->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl">
                                 <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="">
                            </div>
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
                <p class="font-semibold text-stone-700">No bookings found</p>
                <p class="text-xs text-stone-400 mt-1">Try changing the status filter or search query.</p>
                <a href="{{ route('rooms.search') }}"
                   class="mt-4 inline-flex items-center px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 text-sm transition-colors shadow-sm">
                    Browse Rooms
                </a>
            </div>
        @endforelse
    </div>
</div>
