<x-layouts.app>
    <x-slot:title>Home</x-slot:title>

    {{-- ═══ HERO CAROUSEL ══════════════════════════════════════ --}}
    <section x-data="{
        current: 0,
        slides: {{ $carousels->count() }},
        autoplay: null,
        next() { this.current = (this.current + 1) % this.slides; },
        prev() { this.current = (this.current - 1 + this.slides) % this.slides; },
        start() { this.autoplay = setInterval(() => this.next(), 5000); },
        stop() { clearInterval(this.autoplay); }
    }" x-init="slides > 0 && start()" @mouseenter="stop()" @mouseleave="start()"
    class="relative min-h-screen flex items-center overflow-hidden bg-stone-900">

        {{-- Slides --}}
        @forelse($carousels as $index => $slide)
            <div x-show="current === {{ $index }}" x-transition:enter="transition-opacity duration-1000"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-500" x-transition:leave-end="opacity-0"
                 class="absolute inset-0">
                <img src="{{ $slide->image_path }}" alt="{{ $slide->title }}"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-stone-900/80 via-stone-900/50 to-transparent"></div>
            </div>
        @empty
            {{-- Gradient fallback when no carousel images --}}
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-teal-800 to-stone-900">
                <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            </div>
        @endforelse

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
            @if($carousels->count() > 0)
                @foreach($carousels as $index => $slide)
                    <div x-show="current === {{ $index }}" x-transition:enter="transition-all duration-700 delay-300"
                         x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                         class="max-w-2xl">
                        @if($slide->title)
                            <h1 class="text-5xl md:text-7xl font-black text-white leading-tight">
                                {!! nl2br(e($slide->title)) !!}
                            </h1>
                        @endif
                        @if($slide->subtitle)
                            <p class="mt-4 text-lg text-white/80 leading-relaxed">{{ $slide->subtitle }}</p>
                        @endif
                        @if($slide->cta_text)
                            <a href="{{ $slide->cta_url ?? route('rooms.search') }}"
                               class="mt-8 inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl transition-all shadow-xl shadow-emerald-900/30 hover:-translate-y-1">
                                {{ $slide->cta_text }}
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="max-w-2xl">
                    <p class="text-emerald-400 font-semibold tracking-widest text-sm uppercase mb-4">Welcome to</p>
                    <h1 class="text-5xl md:text-7xl font-black text-white leading-tight">
                        Agro-Inland<br>
                        <span class="text-emerald-400">Resort</span>
                    </h1>
                    <p class="mt-6 text-lg text-white/80 leading-relaxed max-w-lg">
                        Escape to nature. Experience farm-fresh air, lush greenery, and modern comfort in the heart of the countryside.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('rooms.search') }}"
                           class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl transition-all shadow-xl hover:-translate-y-1">
                            Browse Rooms
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                        <a href="#location" class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl backdrop-blur-sm transition-all border border-white/20">
                            Find Us
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Carousel Controls --}}
        @if($carousels->count() > 1)
            <button @click="prev()"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all border border-white/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button @click="next()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all border border-white/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Dots --}}
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                @foreach($carousels as $index => $slide)
                    <button @click="current = {{ $index }}"
                            :class="current === {{ $index }} ? 'w-8 bg-white' : 'w-2 bg-white/40'"
                            class="h-2 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
        @endif

        {{-- Scroll Indicator --}}
        <div class="absolute bottom-8 right-8 z-20 flex flex-col items-center gap-2 text-white/50">
            <span class="text-xs font-medium tracking-widest uppercase rotate-90 mb-2">Scroll</span>
            <div class="w-px h-12 bg-gradient-to-b from-white/50 to-transparent animate-pulse"></div>
        </div>
    </section>

    {{-- ═══ QUICK SEARCH BAR ══════════════════════════════════════ --}}
    <section class="relative -mt-8 z-20 max-w-5xl mx-auto px-4">
        <form action="{{ route('rooms.search') }}" method="GET"
              class="bg-white rounded-2xl shadow-2xl shadow-stone-300/40 p-6 border border-stone-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check In</label>
                    <input type="date" name="check_in" value="{{ request('check_in', today()->format('Y-m-d')) }}"
                           min="{{ today()->format('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50">
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check Out</label>
                    <input type="date" name="check_out" value="{{ request('check_out', today()->addDay()->format('Y-m-d')) }}"
                           min="{{ today()->addDay()->format('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50">
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                    <input type="number" name="adults" value="{{ request('adults', 2) }}" min="1" max="20"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50">
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Type</label>
                    <select name="booking_type" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50">
                        <option value="">All Types</option>
                        <option value="overnight">Overnight</option>
                        <option value="day_pass">Day Pass</option>
                    </select>
                </div>
                <div class="lg:col-span-1 flex items-end">
                    <button type="submit"
                            class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-emerald-200">
                        Search Rooms
                    </button>
                </div>
            </div>
        </form>
    </section>

    {{-- ═══ FEATURED ROOMS ══════════════════════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-12">
            <p class="text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-2">Our Accommodations</p>
            <h2 class="text-4xl font-black text-stone-900">Rooms & Cottages</h2>
            <p class="text-stone-500 mt-3 max-w-xl mx-auto">From cozy rooms to private cottages, find your perfect nature retreat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($featuredRooms as $room)
                <a href="{{ route('rooms.show', $room) }}"
                   class="group bg-white rounded-2xl overflow-hidden shadow-md shadow-stone-200/60 hover:shadow-xl hover:shadow-stone-200/80 transition-all duration-300 hover:-translate-y-1">
                    <div class="relative h-56 overflow-hidden bg-stone-100">
                        @if($room->photos->isNotEmpty())
                            <img src="{{ $room->photos->first()->path }}"
                                 alt="{{ $room->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <img src="{{ asset('storage/assets/AIR-LOGO.png') }}"
                                 alt=""
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold text-stone-700 rounded-lg">
                                {{ $room->category->name }}
                            </span>
                        </div>
                        @if($room->allows_pets)
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 bg-amber-100/90 backdrop-blur-sm text-xs font-semibold text-amber-800 rounded-lg flex">
                                    Pet-Friendly &nbsp;<x-icon-paw class="size-5"/></span>
                            </div>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-stone-900 text-lg">{{ $room->name }}</h3>
                                <p class="text-stone-500 text-sm mt-0.5">Room {{ $room->room_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black text-emerald-600">₱{{ number_format($room->base_price, 0) }}</p>
                                <p class="text-xs text-stone-400">/ night</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-4 text-xs text-stone-500">
                            <span class="flex items-center gap-1">
                                <x-icon-users/>
                                {{ $room->capacity_adults }} Adults
                            </span>
                            @if($room->capacity_children > 0)
                                <span class="flex items-center gap-1">
                                    <x-icon-baby/> {{ $room->capacity_children }} Children
                                </span>
                            @endif
                        </div>
                        <div class="mt-4 pt-4 border-t border-stone-100 flex items-center justify-end">
                            
                            <span class="text-sm font-semibold text-emerald-600 group-hover:underline">View Details →</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-16 text-stone-400">
                    <p class="text-lg">No rooms available at the moment. Check back soon!</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('rooms.search') }}"
               class="inline-flex items-center gap-2 px-8 py-4 border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white font-semibold rounded-2xl transition-all">
                View All Rooms
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </section>

    {{-- ═══ AMENITIES ══════════════════════════════════════ --}}
    <section id="amenities" class="bg-gradient-to-br from-emerald-900 to-teal-900 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-emerald-300 font-semibold text-sm uppercase tracking-widest mb-2">What We Offer</p>
                <h2 class="text-4xl font-black text-white">Resort Amenities</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @php
                    $amenities = [
                        ['icon' => '🏊', 'name' => 'Swimming Pool'],
                        ['icon' => '🌿', 'name' => 'Farm Tour'],
                        ['icon' => '🎣', 'name' => 'Fishing Pond'],
                        ['icon' => '🌲', 'name' => 'Nature Trail'],
                        ['icon' => '🍳', 'name' => 'Farm-to-Table'],
                        ['icon' => '🐄', 'name' => 'Animal Farm'],
                    ];
                @endphp
                @foreach($amenities as $amenity)
                    <div class="text-center p-6 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-2xl transition-all hover:-translate-y-1 cursor-default border border-white/10">
                        <div class="text-4xl mb-3">{{ $amenity['icon'] }}</div>
                        <p class="text-white font-semibold text-sm">{{ $amenity['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ GPS LOCATION MAP ══════════════════════════════════════ --}}
    <section id="location" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-2">Find Us</p>
                <h2 class="text-4xl font-black text-stone-900 mb-6">How to Get Here</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-4 bg-stone-50 rounded-2xl">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                            <x-icon-map-pin class="text-red-500"/>
                        </div>
                        <div>
                            <p class="font-semibold text-stone-900">Address</p>
                            <p class="text-stone-600 text-sm mt-0.5">{{ $resortAddress }}</p>
                        </div>
                    </div>
                    @if($contactDetails)
                        <div class="flex items-start gap-4 p-4 bg-stone-50 rounded-2xl">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0"><x-icon-phone /></div>
                            <div>
                                <p class="font-semibold text-stone-900">Contact</p>
                                <p class="text-stone-600 text-sm mt-0.5">{{ $contactDetails }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $resortLat }},{{ $resortLng }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors text-sm">
                        <x-icon-direction/>
                        Get Directions
                    </a>
                    <a href="https://waze.com/ul?ll={{ $resortLat }},{{ $resortLng }}&navigate=yes"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-sky-500 text-white font-semibold rounded-xl hover:bg-sky-600 transition-colors text-sm">
                        <x-icon-waze class="size-6"/> Open in Waze
                    </a>
                </div>
            </div>

            {{-- Leaflet Map --}}
            <div class="rounded-3xl overflow-hidden shadow-2xl shadow-stone-300/40 h-80 lg:h-96" id="resort-map"></div>
        </div>
    </section>

    {{-- Load Leaflet.js --}}
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
                    <div style="background:#059669;width:40px;height:40px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
                        <div style="transform:rotate(45deg);width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            <img src="${logoUrl}" alt="Agro-Inland Resort" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </div>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
            });

            L.marker([lat, lng], { icon })
                .addTo(map)
                .bindPopup('<strong>Agro-Inland Resort</strong><br>' + resortAddress)
                .openPopup();
        });
    </script>
</x-layouts.app>
