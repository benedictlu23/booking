<?php

use App\Models\ResortSetting;
use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public string $checkIn = '';
    public string $checkOut = '';
    public int $adults = 1;
    public int $children = 0;
    public int $pets = 0;
    public string $bookingType = ''; // empty = show all types by default
    public ?int $categoryId = null;
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public string $search = '';
    public string $petPolicy = '';

    public function mount(
        ?string $initialCheckIn = null,
        ?string $initialCheckOut = null,
        ?int $initialAdults = null,
        ?int $initialChildren = null,
        ?int $initialPets = null,
        ?string $initialBookingType = null,
        ?int $initialCategoryId = null,
        ?float $initialMinPrice = null,
        ?float $initialMaxPrice = null
    ): void {
        $this->checkIn = $initialCheckIn ?: request('check_in', today()->format('Y-m-d'));
        $this->checkOut = $initialCheckOut ?: request('check_out', today()->addDay()->format('Y-m-d'));
        $this->adults = (int) ($initialAdults ?: request('adults', 1));
        $this->children = (int) ($initialChildren ?: request('children', 0));
        $this->pets = (int) ($initialPets ?: request('pets', 0));
        $this->bookingType = $initialBookingType ?? request('booking_type', '');
        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $this->checkIn;
        }
        $this->categoryId = $initialCategoryId ? (int) $initialCategoryId : (request('category_id') ? (int) request('category_id') : null);
        $this->minPrice = $initialMinPrice ?: (request('min_price') ? (float) request('min_price') : null);
        $this->maxPrice = $initialMaxPrice ?: (request('max_price') ? (float) request('max_price') : null);
        $this->petPolicy = ResortSetting::get('pet_policy', 'Pets are welcome with a nightly fee.');
    }

    public function updatedBookingType(): void
    {
        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $this->checkIn;
        } else {
            $cIn = Carbon::hasFormat($this->checkIn, 'Y-m-d') ? Carbon::parse($this->checkIn) : today();
            $cOut = Carbon::hasFormat($this->checkOut, 'Y-m-d') ? Carbon::parse($this->checkOut) : today()->addDay();
            if ($cOut->lte($cIn)) {
                $this->checkOut = $cIn->copy()->addDay()->format('Y-m-d');
            }
        }
    }

    public function updatedCheckIn(): void
    {
        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $this->checkIn;
        } else {
            $cIn = Carbon::hasFormat($this->checkIn, 'Y-m-d') ? Carbon::parse($this->checkIn) : today();
            $cOut = Carbon::hasFormat($this->checkOut, 'Y-m-d') ? Carbon::parse($this->checkOut) : today()->addDay();
            if ($cOut->lte($cIn)) {
                $this->checkOut = $cIn->copy()->addDay()->format('Y-m-d');
            }
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryId = null;
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->adults = 1;
        $this->children = 0;
        $this->pets = 0;
        $this->bookingType = '';
    }

    public function getCategoriesProperty(): EloquentCollection
    {
        return RoomCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getRoomsProperty(): Collection
    {
        $checkInDate = Carbon::hasFormat($this->checkIn, 'Y-m-d')
            ? Carbon::parse($this->checkIn)
            : today();

        $checkOutDate = $this->bookingType === 'day_pass'
            ? $checkInDate
            : (Carbon::hasFormat($this->checkOut, 'Y-m-d') ? Carbon::parse($this->checkOut) : today()->addDay());

        if ($this->bookingType !== 'day_pass' && $checkOutDate->lte($checkInDate)) {
            $checkOutDate = $checkInDate->copy()->addDay();
        }

        return Room::with(['category', 'photos', 'amenities'])
            ->where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'out_of_service', 'blocked'])
            ->where('capacity_adults', '>=', max(1, $this->adults))
            ->when($this->pets > 0, fn ($q) => $q->where('allows_pets', true))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->minPrice !== null && $this->minPrice > 0, fn ($q) => $q->where('base_price', '>=', $this->minPrice))
            ->when($this->maxPrice !== null && $this->maxPrice > 0, fn ($q) => $q->where('base_price', '<=', $this->maxPrice))
            ->when($this->bookingType === 'day_pass', fn ($q) => $q->whereIn('booking_type', ['day_pass', 'both']))
            ->when($this->bookingType === 'overnight', fn ($q) => $q->whereIn('booking_type', ['overnight', 'both']))
            ->when(filled($this->search), function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('room_number', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->get()
            ->filter(fn (Room $room) => $room->isAvailableFor(
                $checkInDate,
                $room->booking_type === 'day_pass' ? $checkInDate : $checkOutDate
            ));
    }
}; ?>

<div>
    {{-- Search Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-stone-900">Find Your Perfect Room</h1>
            <p class="text-stone-500 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mr-2">
                    ⚡ Live search
                </span>
                <span class="font-bold text-stone-800">{{ $this->rooms->count() }}</span> room(s) available for your selected criteria
            </p>
        </div>

        {{-- Quick Search Input --}}
        <div class="w-full md:w-72 relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search room"
                   class="w-full pl-9 pr-4 py-2.5 bg-white border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm">
            <span class="absolute left-3 top-3 text-stone-400"><x-icon-search class="size-6"/></span>
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-sm">✕</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Filters Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 sticky top-24 space-y-4">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <h2 class="font-bold text-stone-900 text-base">Refine Search</h2>
                    <button wire:click="resetFilters" type="button" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">
                        Reset All
                    </button>
                </div>

                {{-- Stay Dates --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check In</label>
                    <input type="date"
                           wire:model.live="checkIn"
                           min="{{ today()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide">Check Out</label>
                        @if($bookingType === 'day_pass')
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Locked (Day Pass)
                            </span>
                        @endif
                    </div>
                    <input type="date"
                           wire:model.live="checkOut"
                           @disabled($bookingType === 'day_pass')
                           @readonly($bookingType === 'day_pass')
                           min="{{ $bookingType === 'day_pass' ? $checkIn : today()->addDay()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $bookingType === 'day_pass' ? 'bg-stone-100 text-stone-500 cursor-not-allowed border-dashed' : '' }}">
                    @if($bookingType === 'day_pass')
                        <p class="text-[11px] text-amber-600 mt-1">Same-day departure for Day Pass.</p>
                    @endif
                </div>

                {{-- Guests --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                        <input type="number"
                               wire:model.live="adults"
                               min="1" max="20"
                               class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Children</label>
                        <input type="number"
                               wire:model.live="children"
                               min="0" max="20"
                               class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                {{-- Pets --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Pets 🐾</label>
                    <input type="number"
                           wire:model.live="pets"
                           min="0" max="10"
                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @if($pets > 0 && $petPolicy)
                        <p class="mt-2 text-xs text-amber-800 bg-amber-50 p-2 rounded-lg border border-amber-200">
                            🐾 {{ $petPolicy }}
                        </p>
                    @endif
                </div>

                {{-- Booking Type --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Booking Type</label>
                    <select wire:model.live="bookingType"
                            class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Types</option>
                        <option value="overnight">Overnight Stay</option>
                        <option value="day_pass">Day Pass Only</option>
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Category</label>
                    <select wire:model.live="categoryId"
                            class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Min (₱)</label>
                        <input type="number"
                               wire:model.live.debounce.300ms="minPrice"
                               placeholder="Min"
                               class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Max (₱)</label>
                        <input type="number"
                               wire:model.live.debounce.300ms="maxPrice"
                               placeholder="Max"
                               class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div wire:loading class="w-full text-center py-2 text-xs font-semibold text-emerald-600 bg-emerald-50 rounded-xl">
                    Updating results... ⏳
                </div>
            </div>
        </div>

        {{-- Room Results --}}
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($this->rooms as $room)
                    <div class="group bg-white rounded-2xl overflow-hidden shadow-md shadow-stone-200/60 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                        <div>
                            {{-- Room Image --}}
                            <div class="relative h-52 overflow-hidden bg-stone-100">
                                @if($room->photos->isNotEmpty())
                                    <img src="{{$room->photos->first()->path }}"
                                         alt="{{ $room->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <img src="{{asset('storage/assets/AIR-LOGO.png') }}"
                                         alt=""
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @endif
                                <div class="absolute top-3 left-3 flex gap-2">
                                    <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold text-stone-700 rounded-lg">
                                        {{ $room->category?->name }}
                                    </span>
                                    @if($room->allows_pets)
                                        <span class="px-2.5 py-1 bg-amber-100/90 text-amber-800 text-xs font-semibold rounded-lg">🐾 Pet OK</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-bold text-stone-900 text-lg">{{ $room->name }}</h3>
                                        <p class="text-xs text-stone-400">Room {{ $room->room_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black text-emerald-600">₱{{ number_format($room->base_price, 0) }}</p>
                                        <p class="text-xs text-stone-400">/ night</p>
                                    </div>
                                </div>

                                @if($room->description)
                                    <p class="text-sm text-stone-500 mb-3 line-clamp-2">{{ $room->description }}</p>
                                @endif

                                {{-- Amenities --}}
                                @if($room->amenities->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach($room->amenities->take(4) as $amenity)
                                            <span class="px-2 py-0.5 bg-stone-100 text-stone-600 text-xs rounded-lg">{{ $amenity->name }}</span>
                                        @endforeach
                                        @if($room->amenities->count() > 4)
                                            <span class="px-2 py-0.5 bg-stone-100 text-stone-500 text-xs rounded-lg">+{{ $room->amenities->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 pt-0 border-t border-stone-50 flex items-center justify-between">
                            <span class="text-xs text-stone-500 font-medium">👥 Up to {{ $room->capacity_adults }} adults</span>
                            <a href="{{ route('rooms.show', [
                                'room' => $room->id,
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'adults' => $adults,
                                'children' => $children,
                                'pets' => $pets,
                                'booking_type' => $bookingType
                            ]) }}"
                               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                                Book Now →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-20 bg-white rounded-2xl border border-stone-100">
                        <div class="flex flex-col items-center justify-center w-full">
                            <x-icon-search-alert class="size-15 text-stone-500" />
                        </div>
                        <h3 class="font-bold text-stone-700 text-lg mt-3">No matching rooms found</h3>
                        <p class="text-sm text-stone-400 mt-1 max-w-sm mx-auto">Try adjusting your stay dates, price filters, or guest capacity.</p>
                        <button wire:click="resetFilters"
                                class="mt-4 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                            Reset Filters
                        </button>
</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
