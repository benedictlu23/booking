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
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Guest Portfolio</span>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32] flex items-center gap-3 mt-0.5">
                My Bookings
                <span class="text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded-full font-semibold bg-[#233D32]/10 text-[#233D32]">Live Sync</span>
            </h1>
            <p class="text-xs text-[#777A72] mt-1 font-light">View all your historical and upcoming reservations</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search code or room..."
                       class="w-full sm:w-64 pl-9 pr-4 py-2.5 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] text-[#292D29] placeholder-[#777A72]/60 shadow-2xs">
                <svg class="w-4 h-4 text-[#777A72] absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <a href="{{ route('rooms.search') }}"
               class="px-4 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs uppercase tracking-widest font-semibold rounded-xl transition-all whitespace-nowrap shadow-xs">
                + New Booking
            </a>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1 no-scrollbar">
        @php
            $filters = [
                '' => 'All Bookings',
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
                    class="shrink-0 px-4 py-2 rounded-xl text-xs uppercase tracking-wider font-semibold transition-all {{ $statusFilter === $value ? 'bg-[#233D32] text-[#F7F5EF] shadow-xs' : 'bg-white text-[#777A72] border border-[#233D32]/15 hover:border-[#233D32]/30 hover:text-[#233D32]' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Bookings List --}}
    <div class="space-y-4">
        @forelse($this->bookings as $booking)
            <a href="{{ route('customer.bookings.show', $booking) }}"
               class="group block bg-white rounded-2xl p-5 sm:p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 hover:border-[#233D32]/30 hover:shadow-[0_8px_30px_rgba(35,61,50,0.08)] transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-[#FAF8F4] border border-[#233D32]/10 shrink-0 flex items-center justify-center">
                        @if($booking->room?->photos->first())
                            <img src="{{ $booking->room->photos->first()->path }}"
                                 alt="{{ $booking->room->name }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Resort" class="w-10 h-10 object-contain opacity-70">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="font-mono text-xs font-semibold text-[#A68B61] tracking-wider">{{ $booking->booking_code }}</span>
                            @php
                                $statusBadges = [
                                    'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'confirmed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                    'checked_out' => 'bg-stone-100 text-stone-700 border-stone-200',
                                    'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] uppercase tracking-wider font-semibold border {{ $statusBadges[$booking->status] ?? 'bg-stone-100 text-stone-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </div>
                        <p class="font-serif font-bold text-base sm:text-lg text-[#233D32] group-hover:text-[#A68B61] transition-colors truncate">{{ $booking->room?->name }}</p>
                        <p class="text-xs text-[#777A72] mt-0.5 font-light">
                            {{ $booking->check_in->format('M d') }} → {{ $booking->check_out->format('M d, Y') }}
                            · {{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}
                            · Room {{ $booking->room?->room_number }}
                        </p>
                    </div>
                    <div class="flex sm:flex-col items-center sm:items-end justify-between pt-3 sm:pt-0 border-t sm:border-t-0 border-[#233D32]/5 shrink-0">
                        <p class="font-serif font-bold text-lg text-[#233D32]">₱{{ number_format($booking->total_price, 2) }}</p>
                        <p class="text-[11px] font-medium mt-0.5 {{ $booking->payment_status === 'paid' ? 'text-emerald-700' : 'text-[#A68B61]' }}">
                            {{ ucfirst($booking->payment_status) }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="py-20 text-center bg-white rounded-3xl border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)] px-4">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="font-serif font-bold text-lg text-[#233D32]">No Bookings Found</h3>
                <p class="text-xs text-[#777A72] mt-1 font-light">Try adjusting your search criteria or status filter.</p>
                <a href="{{ route('rooms.search') }}"
                   class="mt-5 inline-flex items-center px-6 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs uppercase tracking-widest font-semibold rounded-xl transition-all shadow-sm">
                    Browse Accommodations
                </a>
            </div>
        @endforelse
    </div>
</div>