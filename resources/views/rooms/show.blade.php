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

                    {{-- Photo Gallery --}}
                    <div x-data="{ active: 0 }" class="rounded-3xl overflow-hidden bg-stone-200 aspect-video">
                        @if($room->photos->isNotEmpty())
                            <div class="relative h-full">
                                @foreach($room->photos as $index => $photo)
                                    <img x-show="active === {{ $index }}"
                                         x-transition:enter="transition-opacity duration-500"
                                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                         src="{{ asset('storage/'.$photo->path) }}"
                                         alt="{{ $photo->caption ?? $room->name }}"
                                         class="absolute inset-0 w-full h-full object-cover">
                                @endforeach
                                @if($room->photos->count() > 1)
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                        @foreach($room->photos as $index => $photo)
                                            <button @click="active = {{ $index }}"
                                                    :class="active === {{ $index }} ? 'bg-white w-6' : 'bg-white/50 w-2'"
                                                    class="h-2 rounded-full transition-all duration-300"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="h-full flex items-center justify-center text-6xl">🌿</div>
                        @endif
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
                            <a href="{{ route('booking.show', array_merge(['room' => $room->id], request()->only(['check_in', 'check_out', 'adults', 'children', 'pets']))) }}"
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
