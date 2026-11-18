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
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#233D32]/10 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EFEBE1] text-[#233D32] text-xs font-semibold uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-[#A68B61]"></span>
                <span>Active Availability</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#233D32]">Accommodations & Suites</h1>
            <p class="text-sm text-[#777A72] mt-1.5 font-light">
                <span class="font-bold text-[#233D32]">{{ $this->rooms->count() }}</span> accommodation unit(s) available for your selected dates and criteria
            </p>
        </div>

        {{-- Quick Search Input --}}
        <div class="w-full md:w-80 relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search accommodation name or unit..."
                   class="w-full pl-10 pr-9 py-3 bg-white border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-xs transition-all">
            <span class="absolute left-3.5 top-3.5 text-[#A68B61]">
                <x-icon-search class="w-4 h-4"/>
            </span>
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-3.5 top-3 text-[#777A72] hover:text-[#233D32] text-sm">✕</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Filters Sidebar (4 cols) --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 sticky top-28 space-y-5">
                <div class="flex items-center justify-between border-b border-[#233D32]/10 pb-3">
                    <h2 class="font-serif font-bold text-[#233D32] text-lg">Filter Stay</h2>
                    <button wire:click="resetFilters" type="button" class="text-xs text-[#A68B61] hover:text-[#8C734D] font-semibold uppercase tracking-wider">
                        Reset All
                    </button>
                </div>

                {{-- Stay Dates --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Check In</label>
                    <input type="date"
                           wire:model.live="checkIn"
                           min="{{ today()->format('Y-m-d') }}"
                           class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Check Out</label>
                        @if($bookingType === 'day_pass')
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#8C734D] bg-[#EFEBE1] px-2 py-0.5 rounded-full border border-[#A68B61]/30">
                                Locked (Day Pass)
                            </span>
                        @endif
                    </div>
                    <input type="date"
                           wire:model.live="checkOut"
                           @disabled($bookingType === 'day_pass')
                           @readonly($bookingType === 'day_pass')
                           min="{{ $bookingType === 'day_pass' ? $checkIn : today()->addDay()->format('Y-m-d') }}"
                           class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] {{ $bookingType === 'day_pass' ? 'opacity-70 cursor-not-allowed border-dashed bg-[#EFEBE1]' : '' }}">
                    @if($bookingType === 'day_pass')
                        <p class="text-[11px] text-[#8C734D] mt-1 font-light">Same-day departure for Day Pass.</p>
                    @endif
                </div>

                {{-- Guests --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Adults</label>
                        <input type="number"
                               wire:model.live="adults"
                               min="1" max="20"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Children</label>
                        <input type="number"
                               wire:model.live="children"
                               min="0" max="20"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                {{-- Pets --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Pets 🐾</label>
                    <input type="number"
                           wire:model.live="pets"
                           min="0" max="10"
                           class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @if($pets > 0 && $petPolicy)
                        <div class="mt-2 text-xs text-[#233D32] bg-[#EFEBE1] p-3 rounded-xl border border-[#233D32]/10 leading-relaxed font-light">
                            🐾 {{ $petPolicy }}
                        </div>
                    @endif
                </div>

                {{-- Booking Type --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Accommodation Type</label>
                    <select wire:model.live="bookingType"
                            class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <option value="">All Accommodation Types</option>
                        <option value="overnight">Overnight Stay</option>
                        <option value="day_pass">Day Pass Only</option>
                    </select>
                </div>

                {{-- Category --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Category</label>
                    <select wire:model.live="categoryId"
                            class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Price Range (₱)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number"
                               wire:model.live.debounce.300ms="minPrice"
                               placeholder="Min (₱)"
                               class="w-full px-3 py-2 border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <input type="number"
                               wire:model.live.debounce.300ms="maxPrice"
                               placeholder="Max (₱)"
                               class="w-full px-3 py-2 border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div wire:loading class="w-full text-center py-2.5 text-xs font-semibold text-[#233D32] bg-[#EFEBE1] rounded-xl border border-[#233D32]/10 animate-pulse">
                    Refreshing availability...
                </div>
            </div>
        </div>

        {{-- Room Results Grid (8 cols) --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($this->rooms as $room)
                    <div class="group bg-white rounded-2xl overflow-hidden border border-[#233D32]/10 shadow-[0_4px_20px_-4px_rgba(35,61,50,0.06)] hover:shadow-[0_12px_35px_-6px_rgba(35,61,50,0.12)] transition-all duration-300 flex flex-col justify-between hover:-translate-y-1">
                        <div>
                            {{-- Image Container --}}
                            <div class="relative h-56 overflow-hidden bg-[#EFEBE1]">
                                @if($room->photos->isNotEmpty())
                                    <img src="{{ $room->photos->first()->path }}"
                                         alt="{{ $room->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-[#FAF8F4] p-6">
                                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}"
                                             alt="Room"
                                             class="w-20 h-20 object-contain opacity-70 group-hover:scale-105 transition-transform duration-500">
                                    </div>
                                @endif
                                <div class="absolute top-3.5 left-3.5 flex gap-2">
                                    <span class="px-2.5 py-1 bg-[#172A22]/85 backdrop-blur-md text-[11px] uppercase tracking-wider font-semibold text-[#F7F5EF] rounded-lg border border-white/10">
                                        {{ $room->category?->name }}
                                    </span>
                                    @if($room->allows_pets)
                                        <span class="px-2 py-1 bg-[#EFEBE1]/90 backdrop-blur-md text-[#233D32] text-[11px] font-semibold rounded-lg border border-[#233D32]/10 shadow-xs">🐾 Pet OK</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="flex justify-between items-start gap-3 mb-2.5">
                                    <div>
                                        <h3 class="font-serif font-bold text-[#233D32] text-xl leading-snug group-hover:text-[#A68B61] transition-colors">
                                            {{ $room->name }}
                                        </h3>
                                        <p class="text-xs text-[#777A72] font-mono mt-0.5 tracking-wide">Unit {{ $room->room_number }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-serif font-bold text-2xl text-[#233D32]">₱{{ number_format($room->base_price, 0) }}</p>
                                        <p class="text-[10px] uppercase tracking-wider text-[#777A72]">/ night</p>
                                    </div>
                                </div>

                                @if($room->description)
                                    <p class="text-xs text-[#595D58] mb-4 line-clamp-2 leading-relaxed font-light">{{ $room->description }}</p>
                                @endif

                                {{-- Amenities Tags --}}
                                @if($room->amenities->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach($room->amenities->take(3) as $amenity)
                                            <span class="px-2.5 py-0.5 bg-[#FAF8F4] text-[#233D32] border border-[#233D32]/10 text-[11px] rounded-md font-medium">{{ $amenity->name }}</span>
                                        @endforeach
                                        @if($room->amenities->count() > 3)
                                            <span class="px-2 py-0.5 bg-[#FAF8F4] text-[#777A72] border border-[#233D32]/10 text-[11px] rounded-md">+{{ $room->amenities->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 pt-3 border-t border-[#233D32]/10 flex items-center justify-between bg-[#FAF8F4]">
                            <span class="text-xs text-[#595D58] font-medium flex items-center gap-1.5">
                                <x-icon-users class="w-3.5 h-3.5 text-[#A68B61]"/>
                                Up to {{ $room->capacity_adults }} adults
                            </span>
                            <a href="{{ route('rooms.show', [
                                'room' => $room->id,
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'adults' => $adults,
                                'children' => $children,
                                'pets' => $pets,
                                'booking_type' => $bookingType
                            ]) }}"
                               class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-wider rounded-xl transition-all shadow-xs hover:-translate-y-0.5">
                                View Details →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-20 bg-white rounded-2xl border border-[#233D32]/10 p-8 shadow-xs">
                        <div class="flex flex-col items-center justify-center w-full">
                            <x-icon-search-alert class="w-12 h-12 text-[#A68B61] opacity-80" />
                        </div>
                        <h3 class="font-serif font-bold text-[#233D32] text-xl mt-4">No matching rooms found</h3>
                        <p class="text-xs text-[#777A72] mt-1.5 max-w-sm mx-auto font-light leading-relaxed">
                            We couldn't find available rooms matching all selected filters. Try broadening your dates or guest capacity.
                        </p>
                        <button wire:click="resetFilters"
                                class="mt-5 px-6 py-2.5 bg-[#233D32] text-[#F7F5EF] text-xs font-semibold uppercase tracking-widest rounded-xl hover:bg-[#172A22] transition-all shadow-xs">
                            Reset Search Filters
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
