<x-layouts.app>
    <x-slot:title>{{ $room->name }}</x-slot:title>

    <div class="pt-20 pb-20 bg-stone-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-stone-500 mb-6 pt-4">
                <a href="{{ route('home') }}" class="hover:text-stone-700">Home</a>
                <span>/</span>
                <a href="{{ route('rooms.search') }}" class="hover:text-stone-700">Rooms</a>
                <span>/</span>
                <span class="text-stone-900 font-medium">{{ $room->name }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Left: Room Details --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Photo Gallery Tour --}}
                    @php
                        $galleryImages = collect();
                        if ($room->photos->isNotEmpty()) {
                            foreach($room->photos as $p) {
                                $galleryImages->push(['url' => asset('storage/'.$p->path), 'caption' => $p->caption ?? $room->name]);
                            }
                        }
                        if (!empty($room->gallery) && is_array($room->gallery)) {
                            foreach($room->gallery as $gPath) {
                                $galleryImages->push(['url' => asset('storage/'.$gPath), 'caption' => $room->name]);
                            }
                        }
                    @endphp

                    <div x-data="{ active: 0, total: {{ $galleryImages->count() }}, modalOpen: false }" class="space-y-3">
                        <div class="relative rounded-3xl overflow-hidden bg-stone-900 aspect-video group">
                            @if($galleryImages->isNotEmpty())
                                <div class="relative h-full w-full">
                                    @foreach($galleryImages as $index => $photo)
                                        <div x-show="active === {{ $index }}"
                                             x-transition:enter="transition-opacity duration-300"
                                             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                             class="absolute inset-0">
                                            <img src="{{ $photo['url'] }}"
                                                 alt="{{ $photo['caption'] }}"
                                                 @click="modalOpen = true"
                                                 class="w-full h-full object-cover cursor-pointer">
                                        </div>
                                    @endforeach

                                    {{-- Navigation Arrows --}}
                                    @if($galleryImages->count() > 1)
                                        <button type="button" @click="active = (active === 0 ? total - 1 : active - 1)"
                                                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-colors">
                                            ‹
                                        </button>
                                        <button type="button" @click="active = (active === total - 1 ? 0 : active + 1)"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-colors">
                                            ›
                                        </button>
                                    @endif

                                    {{-- Badge / Counter --}}
                                    <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-md px-3 py-1 rounded-full text-white text-xs font-semibold flex items-center gap-2">
                                        <span>📷 <span x-text="active + 1"></span> / {{ $galleryImages->count() }}</span>
                                        <button type="button" @click="modalOpen = true" class="hover:underline">Fullscreen ⛶</button>
                                    </div>
                                </div>
                            @else
                                <div class="h-full flex items-center justify-center text-6xl">🌿</div>
                            @endif
                        </div>

                        {{-- Thumbnail Tour Strip --}}
                        @if($galleryImages->count() > 1)
                            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                                @foreach($galleryImages as $index => $photo)
                                    <button type="button" @click="active = {{ $index }}"
                                            :class="active === {{ $index }} ? 'ring-2 ring-emerald-500 scale-105' : 'opacity-60 hover:opacity-100'"
                                            class="shrink-0 w-20 h-14 rounded-xl overflow-hidden bg-stone-200 transition-all">
                                        <img src="{{ $photo['url'] }}" alt="thumb" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Fullscreen Modal Tour --}}
                        <template x-if="modalOpen">
                            <div class="fixed inset-0 z-50 bg-black/90 flex flex-col items-center justify-center p-4"
                                 @keydown.escape.window="modalOpen = false">
                                <button type="button" @click="modalOpen = false" class="absolute top-6 right-6 text-white text-3xl font-light hover:text-stone-300">✕</button>
                                <div class="max-w-5xl max-h-[80vh] flex items-center justify-center">
                                    <img :src="@json($galleryImages->pluck('url'))[active]"
                                         class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl">
                                </div>
                                <div class="flex items-center gap-4 mt-4 text-white text-sm">
                                    <button type="button" @click="active = (active === 0 ? total - 1 : active - 1)" class="px-3 py-1 bg-white/20 rounded-lg hover:bg-white/30">Previous</button>
                                    <span>Photo <span x-text="active + 1"></span> of {{ $galleryImages->count() }}</span>
                                    <button type="button" @click="active = (active === total - 1 ? 0 : active + 1)" class="px-3 py-1 bg-white/20 rounded-lg hover:bg-white/30">Next</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Info --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg">{{ $room->category->name }}</span>
                                <h1 class="text-3xl font-black text-stone-900 mt-2">{{ $room->name }}</h1>
                                <p class="text-stone-500">Room {{ $room->room_number }}</p>
                            </div>
                            @if($room->allows_pets)
                                <span class="px-3 py-2 bg-amber-50 text-amber-800 text-sm font-semibold rounded-xl border border-amber-200">🐾 Pet-Friendly</span>
                            @endif
                        </div>

                        @if($room->description)
                            <p class="text-stone-600 leading-relaxed">{{ $room->description }}</p>
                        @endif

                        <div class="grid grid-cols-3 gap-4 mt-6 p-4 bg-stone-50 rounded-xl">
                            <div class="text-center">
                                <p class="text-2xl">👥</p>
                                <p class="font-bold text-stone-900">{{ $room->capacity_adults }}</p>
                                <p class="text-xs text-stone-500">Adults</p>
                            </div>
                            @if($room->capacity_children > 0)
                                <div class="text-center">
                                    <p class="text-2xl">👶</p>
                                    <p class="font-bold text-stone-900">{{ $room->capacity_children }}</p>
                                    <p class="text-xs text-stone-500">Children</p>
                                </div>
                            @endif
                            @if($room->allows_pets)
                                <div class="text-center">
                                    <p class="text-2xl">🐾</p>
                                    <p class="font-bold text-stone-900">+₱{{ number_format($room->pet_fee, 0) }}</p>
                                    <p class="text-xs text-stone-500">Pet Fee/Night</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Amenities --}}
                    @if($room->amenities->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                            <h2 class="font-bold text-stone-900 mb-4">Room Amenities</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($room->amenities as $amenity)
                                    <div class="flex items-center gap-2 text-sm text-stone-700">
                                        <span class="text-emerald-500">✓</span>
                                        {{ $amenity->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Add-ons --}}
                    @if($addons->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                            <h2 class="font-bold text-stone-900 mb-2">Available Add-ons</h2>
                            <p class="text-sm text-stone-500 mb-4">Enhance your stay with our optional extras</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($addons as $addon)
                                    <div class="flex items-start gap-3 p-3 border border-stone-100 rounded-xl hover:border-emerald-200 transition-colors">
                                        @if($addon->icon)
                                            <span class="text-2xl">{{ $addon->icon }}</span>
                                        @else
                                            <span class="text-2xl">✨</span>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-stone-900 text-sm">{{ $addon->name }}</p>
                                            @if($addon->description)
                                                <p class="text-xs text-stone-500">{{ $addon->description }}</p>
                                            @endif
                                            <p class="text-emerald-600 font-bold text-sm mt-1">₱{{ number_format($addon->price, 2) }} <span class="text-stone-400 font-normal">/ {{ str_replace('_', ' ', $addon->pricing_type) }}</span></p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: Booking Widget --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-xl shadow-stone-200/50 border border-stone-100 p-6 sticky top-24">
                        <div class="flex items-end gap-2 mb-6">
                            <span class="text-3xl font-black text-emerald-600">₱{{ number_format($room->base_price, 0) }}</span>
                            <span class="text-stone-400 text-sm mb-1">/ night</span>
                        </div>

                        @auth
                            <a href="{{ route('booking.show', $room) }}?{{ http_build_query(request()->only(['check_in', 'check_out', 'adults', 'children', 'pets'])) }}"
                               class="block w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-center rounded-2xl transition-all shadow-lg shadow-emerald-200 hover:-translate-y-0.5 text-sm">
                                Book This Room
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-center rounded-2xl transition-all shadow-lg shadow-emerald-200 hover:-translate-y-0.5 text-sm">
                                Sign In to Book
                            </a>
                            <p class="text-center text-xs text-stone-400 mt-2">
                                No account? <a href="{{ route('register') }}" class="text-emerald-600 font-medium">Register free</a>
                            </p>
                        @endauth

                        @if($room->allows_pets)
                            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                                🐾 {{ $petPolicy }}
                            </div>
                        @endif

                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-stone-500">
                                <span>Base price</span>
                                <span>₱{{ number_format($room->base_price, 2) }}/night</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
