<x-layouts.app>
    <x-slot:title>Countryside Nature Retreat</x-slot:title>

    {{-- ═══ EDITORIAL HERO SECTION ══════════════════════════════════ --}}
    <section x-data="{
        current: 0,
        slides: {{ $carousels->count() }},
        autoplay: null,
        next() { this.current = (this.current + 1) % this.slides; },
        prev() { this.current = (this.current - 1 + this.slides) % this.slides; },
        start() { this.autoplay = setInterval(() => this.next(), 6000); },
        stop() { clearInterval(this.autoplay); }
    }" x-init="slides > 0 && start()" @mouseenter="stop()" @mouseleave="start()"
    class="relative min-h-[90vh] lg:min-h-screen flex items-end pb-24 pt-32 overflow-hidden bg-[#172A22]">

        {{-- Carousel Slides Background --}}
        @forelse($carousels as $index => $slide)
            <div x-show="current === {{ $index }}"
                 x-transition:enter="transition-opacity duration-1000"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-700"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0">
                <img src="{{ $slide->image_path }}" alt="{{ $slide->title ?? 'Agro-Inland Resort' }}"
                     class="w-full h-full object-cover">
                {{-- Atmospheric Countryside Overlays --}}
                <div class="absolute inset-0 bg-gradient-to-t from-[#172A22] via-[#172A22]/40 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#172A22]/85 via-[#172A22]/50 to-transparent"></div>
            </div>
        @empty
            <div class="absolute inset-0 bg-[#233D32]">
                <div class="absolute inset-0 bg-gradient-to-t from-[#172A22] via-[#233D32]/80 to-[#172A22]/40"></div>
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#A68B61 1px, transparent 1px); background-size: 32px 32px;"></div>
            </div>
        @endforelse

        {{-- Editorial Hero Content (Asymmetrical Layout) --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
                <div class="lg:col-span-8">
                    {{-- Bronze Subtitle Badge --}}
                    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-[#172A22]/70 backdrop-blur-md border border-[#A68B61]/40 mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#A68B61] animate-pulse"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#EFEBE1]">Welcome to Agro-Inland Resort</span>
                    </div>

                    @if($carousels->count() > 0)
                        @foreach($carousels as $index => $slide)
                            <div x-show="current === {{ $index }}"
                                 x-transition:enter="transition-all duration-700 delay-200"
                                 x-transition:enter-start="opacity-0 translate-y-6"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-serif font-bold text-[#F7F5EF] leading-[1.08] tracking-tight max-w-3xl">
                                    @if($slide->title)
                                        {!! nl2br(e($slide->title)) !!}
                                    @else
                                        Find Your Quiet<br>
                                        <span class="italic font-normal text-[#A68B61]">Among Nature.</span>
                                    @endif
                                </h1>
                                <p class="mt-5 text-base sm:text-lg text-[#FAF8F4]/85 leading-relaxed max-w-xl font-light">
                                    {{ $slide->subtitle ?: 'Discover a countryside escape where nature, comfort, and memorable experiences come together.' }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        <div>
                            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-serif font-bold text-[#F7F5EF] leading-[1.08] tracking-tight max-w-3xl">
                                Find Your Quiet<br>
                                <span class="italic font-normal text-[#A68B61]">Among Nature.</span>
                            </h1>
                            <p class="mt-5 text-base sm:text-lg text-[#FAF8F4]/85 leading-relaxed max-w-xl font-light">
                                Discover a countryside escape where nature, comfort, and memorable experiences come together.
                            </p>
                        </div>
                    @endif

                    {{-- Hero Actions --}}
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('rooms.search') }}"
                           class="inline-flex items-center gap-2.5 px-7 py-4 bg-[#A68B61] hover:bg-[#8C734D] text-[#172A22] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_20px_rgba(166,139,97,0.3)] hover:-translate-y-0.5">
                            Browse Rooms
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="#location"
                           class="inline-flex items-center gap-2 px-6 py-4 bg-white/10 hover:bg-white/20 text-[#FAF8F4] font-medium rounded-xl text-xs uppercase tracking-wider backdrop-blur-md transition-all border border-white/20">
                            Explore Location
                        </a>
                    </div>
                </div>

                {{-- Carousel Indicator & Controls (Right Col) --}}
                @if($carousels->count() > 1)
                    <div class="lg:col-span-4 flex lg:flex-col items-center lg:items-end justify-between gap-4">
                        <div class="flex items-center gap-2">
                            @foreach($carousels as $index => $slide)
                                <button @click="current = {{ $index }}"
                                        aria-label="Slide {{ $index + 1 }}"
                                        :class="current === {{ $index }} ? 'w-8 bg-[#A68B61]' : 'w-2 bg-white/40 hover:bg-white/70'"
                                        class="h-1.5 rounded-full transition-all duration-300"></button>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="prev()" aria-label="Previous slide"
                                    class="w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md text-white flex items-center justify-center border border-white/20 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button @click="next()" aria-label="Next slide"
                                    class="w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md text-white flex items-center justify-center border border-white/20 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══ PREMIUM HORIZONTAL RESERVATION PANEL ════════════════════ --}}
    <section class="relative -mt-12 z-20 max-w-6xl mx-auto px-4 sm:px-6">
        <form action="{{ route('rooms.search') }}" method="GET"
              class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_15px_45px_-10px_rgba(35,61,50,0.15)] border border-[#233D32]/10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-5 items-end">

                {{-- Check In --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#777A72]">
                        Check In
                    </label>
                    <div class="relative">
                        <input type="date" name="check_in"
                               value="{{ request('check_in', today()->format('Y-m-d')) }}"
                               min="{{ today()->format('Y-m-d') }}"
                               class="w-full px-3.5 py-3 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] transition-all">
                    </div>
                </div>

                {{-- Check Out --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#777A72]">
                        Check Out
                    </label>
                    <div class="relative">
                        <input type="date" name="check_out"
                               value="{{ request('check_out', today()->addDay()->format('Y-m-d')) }}"
                               min="{{ today()->addDay()->format('Y-m-d') }}"
                               class="w-full px-3.5 py-3 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] transition-all">
                    </div>
                </div>

                {{-- Guests --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#777A72]">
                        Guests
                    </label>
                    <div class="relative">
                        <input type="number" name="adults"
                               value="{{ request('adults', 2) }}" min="1" max="20"
                               class="w-full px-3.5 py-3 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] transition-all">
                    </div>
                </div>

                {{-- Booking Type --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#777A72]">
                        Stay Type
                    </label>
                    <div class="relative">
                        <select name="booking_type"
                                class="w-full px-3.5 py-3 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] transition-all">
                            <option value="">All Accommodations</option>
                            <option value="overnight" {{ request('booking_type') === 'overnight' ? 'selected' : '' }}>Overnight Stay</option>
                            <option value="day_pass" {{ request('booking_type') === 'day_pass' ? 'selected' : '' }}>Day Pass Experience</option>
                        </select>
                    </div>
                </div>

                {{-- Search Button --}}
                <div>
                    <button type="submit"
                            class="w-full py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_15px_rgba(35,61,50,0.2)] hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <x-icon-search class="w-4 h-4 text-[#A68B61]"/>
                        <span>Search Rooms</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    {{-- ═══ ACCOMMODATIONS CATALOGUE ═══════════════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6 border-b border-[#233D32]/10 pb-8">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-[#A68B61] mb-2">Refined Living</p>
                <h2 class="text-3xl sm:text-5xl font-serif font-bold text-[#233D32]">Rooms & Cottages</h2>
                <p class="text-sm text-[#777A72] mt-2 max-w-lg font-light">
                    From intimate garden chambers to traditional thatched cottages, each accommodation is intentionally designed for deep countryside relaxation.
                </p>
            </div>
            <a href="{{ route('rooms.search') }}"
               class="inline-flex items-center gap-2 text-xs uppercase tracking-widest font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors group self-start md:self-end">
                <span>View Full Collection</span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </div>

        {{-- Room Listings Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($featuredRooms as $room)
                <a href="{{ route('rooms.show', $room) }}"
                   class="group bg-white rounded-2xl overflow-hidden border border-[#233D32]/10 shadow-[0_4px_20px_-4px_rgba(35,61,50,0.06)] hover:shadow-[0_12px_35px_-6px_rgba(35,61,50,0.12)] transition-all duration-300 flex flex-col justify-between hover:-translate-y-1">
                    <div>
                        {{-- Image Frame --}}
                        <div class="relative h-64 overflow-hidden bg-[#EFEBE1]">
                            @if($room->photos->isNotEmpty())
                                <img src="{{ $room->photos->first()->path }}"
                                     alt="{{ $room->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-[#FAF8F4] p-8">
                                    <img src="{{ asset('storage/assets/AIR-LOGO.png') }}"
                                         alt="Resort Room"
                                         class="w-24 h-24 object-contain opacity-70 group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @endif

                            {{-- Category Badge --}}
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-[#172A22]/85 backdrop-blur-md text-[11px] uppercase tracking-wider font-semibold text-[#F7F5EF] rounded-lg border border-white/10">
                                    {{ $room->category->name }}
                                </span>
                            </div>

                            {{-- Pet Friendly Indicator --}}
                            @if($room->allows_pets)
                                <div class="absolute top-4 right-4">
                                    <span class="px-2.5 py-1 bg-[#EFEBE1]/90 backdrop-blur-md text-[11px] font-semibold text-[#233D32] rounded-lg border border-[#233D32]/10 flex items-center gap-1 shadow-xs">
                                        <x-icon-paw class="w-3.5 h-3.5 text-[#A68B61]"/>
                                        <span>Pet Welcome</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Details Body --}}
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-serif font-bold text-xl text-[#233D32] group-hover:text-[#A68B61] transition-colors leading-snug">
                                        {{ $room->name }}
                                    </h3>
                                    <p class="text-xs text-[#777A72] font-mono mt-0.5 tracking-wide">Unit {{ $room->room_number }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-serif font-bold text-2xl text-[#233D32]">₱{{ number_format($room->base_price, 0) }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-[#777A72]">/ night</p>
                                </div>
                            </div>

                            {{-- Capacity Indicators --}}
                            <div class="flex items-center gap-5 mt-5 pt-4 border-t border-[#233D32]/10 text-xs text-[#595D58]">
                                <span class="inline-flex items-center gap-1.5 font-medium">
                                    <x-icon-users class="w-4 h-4 text-[#A68B61]"/>
                                    {{ $room->capacity_adults }} Adults
                                </span>
                                @if($room->capacity_children > 0)
                                    <span class="inline-flex items-center gap-1.5 font-medium">
                                        <x-icon-baby class="w-4 h-4 text-[#A68B61]"/>
                                        {{ $room->capacity_children }} Children
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-6 py-4 bg-[#FAF8F4] border-t border-[#233D32]/10 flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider font-semibold text-[#A68B61]">Reserve Room</span>
                        <span class="text-xs font-semibold text-[#233D32] group-hover:translate-x-1 transition-transform">View Details →</span>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-20 bg-white rounded-2xl border border-[#233D32]/10">
                    <p class="font-serif text-lg text-[#233D32]">No accommodations currently listed.</p>
                    <p class="text-xs text-[#777A72] mt-1">Please check back soon or browse all dates.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-14">
            <a href="{{ route('rooms.search') }}"
               class="inline-flex items-center gap-2 px-8 py-4 bg-[#EFEBE1] hover:bg-[#233D32] text-[#233D32] hover:text-[#F7F5EF] font-semibold text-xs uppercase tracking-widest rounded-xl transition-all border border-[#233D32]/15 shadow-xs">
                <span>Explore All Accommodations</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </section>

    {{-- ═══ RESORT AMENITIES ════════════════════════════════════════ --}}
    <section id="amenities" class="bg-[#233D32] text-[#FAF8F4] py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl mx-auto text-center mb-16">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-[#A68B61] mb-2">Experiences & Facilities</p>
                <h2 class="text-3xl sm:text-5xl font-serif font-bold text-[#F7F5EF]">Country Experiences</h2>
                <p class="text-sm text-[#A8B29A] mt-3 font-light leading-relaxed">
                    Designed to connect guests with the earth, fresh air, and relaxing countryside pursuits.
                </p>
            </div>

            @php
                $amenities = [
                    ['icon' => '🏊', 'name' => 'Natural Swimming Pool', 'desc' => 'Freshwater pool surrounded by tropical vegetation'],
                    ['icon' => '🌿', 'name' => 'Farm & Garden Tours', 'desc' => 'Guided walks through organic crops & orchards'],
                    ['icon' => '🎣', 'name' => 'Catch-and-Release Pond', 'desc' => 'Quiet recreational freshwater fishing'],
                    ['icon' => '🌲', 'name' => 'Countryside Nature Trail', 'desc' => 'Scenic walking pathways under bamboo and palm canopies'],
                    ['icon' => '🍳', 'name' => 'Farm-to-Table Kitchen', 'desc' => 'Authentic home-cooked meals using freshly harvested ingredients'],
                    ['icon' => '🐄', 'name' => 'Animal Sanctuary', 'desc' => 'Gentle countryside animal encounters for all ages'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($amenities as $amenity)
                    <div class="p-8 rounded-2xl bg-[#172A22]/70 border border-[#A68B61]/20 hover:border-[#A68B61]/60 transition-all duration-300 hover:-translate-y-1">
                        <div class="text-3xl mb-4">{{ $amenity['icon'] }}</div>
                        <h3 class="font-serif font-bold text-lg text-[#F7F5EF] mb-1.5">{{ $amenity['name'] }}</h3>
                        <p class="text-xs text-[#A8B29A] leading-relaxed font-light">{{ $amenity['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ DESTINATION & GPS LOCATION MAP ══════════════════════════ --}}
    <section id="location" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            {{-- Info Column --}}
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-[#A68B61] mb-2">The Destination</p>
                    <h2 class="text-3xl sm:text-5xl font-serif font-bold text-[#233D32] leading-tight">Find Your Way Here</h2>
                    <p class="text-sm text-[#777A72] mt-3 font-light leading-relaxed">
                        Nestled in the lush agricultural countryside, Agro-Inland Resort is easily accessible while remaining secluded from urban noise.
                    </p>
                </div>

                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-[#233D32]/10 shadow-xs">
                        <div class="w-10 h-10 rounded-xl bg-[#EFEBE1] flex items-center justify-center shrink-0 text-[#233D32]">
                            <x-icon-map-pin class="w-5 h-5 text-[#233D32]"/>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#A68B61]">Resort Address</p>
                            <p class="text-sm text-[#292D29] mt-0.5 font-medium leading-relaxed">{{ $resortAddress }}</p>
                        </div>
                    </div>

                    @if($contactDetails)
                        <div class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-[#233D32]/10 shadow-xs">
                            <div class="w-10 h-10 rounded-xl bg-[#EFEBE1] flex items-center justify-center shrink-0 text-[#233D32]">
                                <x-icon-phone class="w-5 h-5 text-[#233D32]"/>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#A68B61]">Inquiries & Front Desk</p>
                                <p class="text-sm text-[#292D29] mt-0.5 font-medium leading-relaxed">{{ $contactDetails }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Direct Navigation Links --}}
                <div class="pt-2 flex flex-wrap gap-3 items-center">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $resortLat }},{{ $resortLng }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-wider transition-all shadow-xs">
                        <x-icon-direction class="w-4 h-4 text-[#A68B61]"/>
                        <span>Google Maps</span>
                    </a>
                    <a href="https://waze.com/ul?ll={{ $resortLat }},{{ $resortLng }}&navigate=yes"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-3.5 bg-[#EFEBE1] hover:bg-[#E5E0D4] text-[#233D32] font-semibold rounded-xl text-xs uppercase tracking-wider transition-all border border-[#233D32]/10">
                        <x-icon-waze class="w-5 h-5 text-[#233D32]"/>
                        <span>Open Waze</span>
                    </a>
                    <a href="https://www.facebook.com/messages/t/532782233244000"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-3.5 bg-[#EFEBE1] hover:bg-[#E5E0D4] text-[#233D32] font-semibold rounded-xl text-xs uppercase tracking-wider transition-all border border-[#233D32]/10">
                        <x-icon-messenger class="w-5 h-5 text-[#233D32]"/>
                        <span>Message on Facebook</span>
                    </a>
                </div>
            </div>

            {{-- Leaflet Map Frame --}}
            <div class="lg:col-span-7">
                <div class="rounded-3xl overflow-hidden shadow-[0_15px_45px_-10px_rgba(35,61,50,0.18)] border border-[#233D32]/15 h-88 lg:h-[420px] relative" id="resort-map"></div>
            </div>
        </div>
    </section>

    {{-- Leaflet.js Dependencies (Exact behavior & coordinates preserved) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ $resortLat }};
            const lng = {{ $resortLng }};
            const map = L.map('resort-map', { zoomControl: true }).setView([lat, lng], 15);
            const logoUrl = @json(asset('storage/assets/AIR-LOGO.png'));
            const resortAddress = @json($resortAddress);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const icon = L.divIcon({
                html: `
                    <div style="background:#233D32;width:42px;height:42px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 4px 14px rgba(23,42,34,0.4);border:2px solid #A68B61;">
                        <div style="transform:rotate(45deg);width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            <img src="${logoUrl}" alt="Agro-Inland Resort" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </div>
                    </div>
                `,
                iconSize: [42, 42],
                iconAnchor: [21, 42],
            });

            L.marker([lat, lng], { icon })
                .addTo(map)
                .bindPopup('<strong style="font-family:serif;font-size:14px;color:#233D32;">Agro-Inland Resort</strong><br><span style="font-size:12px;color:#595D58;">' + resortAddress + '</span>')
                .openPopup();
        });
    </script>
</x-layouts.app>
