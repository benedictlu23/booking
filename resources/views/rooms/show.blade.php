<x-layouts.app>
    <x-slot:title>{{ $room->name }} | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-24 bg-[#F7F5EF] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#777A72] mb-8">
                <a href="{{ route('home') }}" class="hover:text-[#233D32] transition-colors">Home</a>
                <span class="text-[#A68B61]">/</span>
                <a href="{{ route('rooms.search') }}" class="hover:text-[#233D32] transition-colors">Accommodations</a>
                <span class="text-[#A68B61]">/</span>
                <span class="text-[#233D32] font-bold">{{ $room->name }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

                {{-- Left: Room Details (8 cols) --}}
                <div class="lg:col-span-8 space-y-8">

                    {{-- Gallery / Carousel --}}
                    @php
                        $gallery = [];

                        if ($room->photos->isNotEmpty()) {
                            $gallery = $room->photos
                                ->filter(fn($p) => !empty($p->path))
                                ->map(fn($p) => [
                                    'src' => Str::startsWith($p->path, ['http://', 'https://']) ? $p->path : asset('storage/' . $p->path),
                                    'alt' => $room->name,
                                    'title' => $p->name ?? '',
                                    'description' => $p->description ?? $room->name,
                                ])
                                ->values()
                                ->toArray();
                        } elseif (!empty($room->gallery) && is_array($room->gallery)) {
                            $gallery = collect($room->gallery)
                                ->filter(fn($gPath) => !empty($gPath))
                                ->map(fn($gPath) => [
                                    'src' => Str::startsWith($gPath, ['http://', 'https://']) ? $gPath : asset('storage/' . $gPath),
                                    'alt' => $room->name,
                                    'title' => $room->name,
                                ])
                                ->values()
                                ->toArray();
                        }
                    @endphp

                    <div class="rounded-3xl overflow-hidden border border-[#233D32]/10 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.08)] bg-white p-2">
                        @if($gallery)
                            <x-carousel :images="$gallery" thumbnails clickable autoplay navigable caption="overlay"/>
                        @else
                            <div class="relative rounded-2xl overflow-hidden bg-[#FAF8F4] aspect-video flex items-center justify-center">
                                <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="{{ $room->name }}" class="w-32 h-32 object-contain opacity-60">
                            </div>
                        @endif
                    </div>

                    {{-- Room Overview Card --}}
                    <div class="bg-white rounded-3xl p-8 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                            <div>
                                <span class="px-3 py-1 bg-[#EFEBE1] text-[#233D32] text-xs uppercase tracking-wider font-semibold rounded-lg border border-[#233D32]/10">
                                    {{ $room->category->name }}
                                </span>
                                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#233D32] mt-3">{{ $room->name }}</h1>
                                <p class="text-xs text-[#777A72] font-mono mt-1">Accommodation Unit {{ $room->room_number }}</p>
                            </div>
                            @if($room->allows_pets)
                                <div class="shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#EFEBE1] text-[#233D32] text-xs font-semibold rounded-xl border border-[#A68B61]/30 shadow-xs">
                                        <x-icon-paw class="w-4 h-4 text-[#A68B61]"/>
                                        <span>Pet Welcome</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($room->description)
                            <p class="text-sm text-[#595D58] leading-relaxed font-light border-t border-[#233D32]/10 pt-5">{{ $room->description }}</p>
                        @endif

                        {{-- Capacity Metrics Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 p-5 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/5">
                            <div class="text-center p-3">
                                <div class="flex justify-center mb-1 text-[#A68B61]">
                                    <x-icon-users class="w-5 h-5" />
                                </div>
                                <p class="font-serif font-bold text-xl text-[#233D32]">{{ $room->capacity_adults }}</p>
                                <p class="text-[11px] uppercase tracking-wider text-[#777A72] font-medium">Adults</p>
                            </div>
                            @if($room->capacity_children > 0)
                                <div class="text-center p-3">
                                    <div class="flex justify-center mb-1 text-[#A68B61]">
                                        <x-icon-baby class="w-5 h-5" />
                                    </div>
                                    <p class="font-serif font-bold text-xl text-[#233D32]">{{ $room->capacity_children }}</p>
                                    <p class="text-[11px] uppercase tracking-wider text-[#777A72] font-medium">Children</p>
                                </div>
                            @endif
                            @if($room->allows_pets)
                                <div class="text-center p-3 col-span-2 sm:col-span-1">
                                    <div class="flex justify-center mb-1 text-[#A68B61]">
                                        <x-icon-paw class="w-5 h-5" />
                                    </div>
                                    <p class="font-serif font-bold text-xl text-[#233D32]">+₱{{ number_format($room->pet_fee, 0) }}</p>
                                    <p class="text-[11px] uppercase tracking-wider text-[#777A72] font-medium">Pet Fee / Night</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Room Amenities --}}
                    @if($room->amenities->isNotEmpty())
                        <div class="bg-white rounded-3xl p-8 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10">
                            <h2 class="font-serif font-bold text-2xl text-[#233D32] mb-6">Room Amenities</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                                @foreach($room->amenities as $amenity)
                                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-[#FAF8F4] border border-[#233D32]/5 text-xs font-medium text-[#292D29]">
                                        <span class="w-4 h-4 rounded-full bg-[#233D32] text-[#F7F5EF] flex items-center justify-center text-[10px]">✓</span>
                                        <span>{{ $amenity->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Available Add-ons --}}
                    @if($addons->isNotEmpty())
                        <div class="bg-white rounded-3xl p-8 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10">
                            <h2 class="font-serif font-bold text-2xl text-[#233D32] mb-1.5">Optional Add-On Services</h2>
                            <p class="text-xs text-[#777A72] font-light mb-6">Enhance your countryside retreat with tailored extras</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($addons as $addon)
                                    <div class="p-4 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-start gap-3.5 hover:border-[#A68B61]/50 transition-colors">
                                        <span class="text-2xl">{{ $addon->icon ?: '🌿' }}</span>
                                        <div>
                                            <p class="font-serif font-bold text-sm text-[#233D32]">{{ $addon->name }}</p>
                                            @if($addon->description)
                                                <p class="text-xs text-[#777A72] font-light mt-0.5 leading-relaxed">{{ $addon->description }}</p>
                                            @endif
                                            <p class="text-xs font-semibold text-[#A68B61] mt-2">
                                                ₱{{ number_format($addon->price, 2) }}
                                                <span class="text-[#777A72] font-normal">/ {{ str_replace('_', ' ', $addon->pricing_type) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: Sticky Booking Widget (4 cols) --}}
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(35,61,50,0.1)] border border-[#233D32]/10 p-7 sticky top-28 space-y-6">
                        <div class="flex items-baseline justify-between border-b border-[#233D32]/10 pb-5">
                            <div>
                                <span class="text-xs uppercase tracking-wider text-[#777A72] block">Standard Rate</span>
                                <span class="font-serif font-bold text-3xl text-[#233D32]">₱{{ number_format($room->base_price, 0) }}</span>
                                <span class="text-xs text-[#777A72] font-light">/ night</span>
                            </div>
                            <span class="px-2.5 py-1 bg-[#EFEBE1] text-[#233D32] text-xs font-semibold rounded-lg capitalize">
                                {{ str_replace('_', ' ', $room->booking_type) }}
                            </span>
                        </div>

                        @php
                            $isRoomBlocked = in_array($room->status, ['maintenance', 'out_of_service', 'blocked']) || ! $room->is_active;
                        @endphp

                        @if($isRoomBlocked)
                            <div class="p-5 bg-rose-50 border border-rose-200 rounded-2xl text-center space-y-2">
                                <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-lg inline-block">
                                    Unavailable — {{ ucfirst(str_replace('_', ' ', $room->status)) }}
                                </span>
                                <p class="text-xs text-rose-700 leading-relaxed font-light">
                                    This unit is currently unavailable for direct reservations. Please explore other available cottages.
                                </p>
                            </div>
                        @else
                            @auth
                                <a href="{{ route('booking.show', $room) }}?{{ http_build_query(request()->only(['check_in', 'check_out', 'adults', 'children', 'pets', 'booking_type'])) }}"
                                   class="block w-full py-4 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold text-center text-xs uppercase tracking-widest rounded-xl transition-all shadow-[0_4px_15px_rgba(35,61,50,0.2)] hover:-translate-y-0.5">
                                    Reserve This Room
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="block w-full py-4 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold text-center text-xs uppercase tracking-widest rounded-xl transition-all shadow-[0_4px_15px_rgba(35,61,50,0.2)] hover:-translate-y-0.5">
                                    Sign In to Reserve
                                </a>
                                <p class="text-center text-xs text-[#777A72] font-light">
                                    No guest account yet? <a href="{{ route('register') }}" class="text-[#233D32] font-semibold underline underline-offset-2">Register free</a>
                                </p>
                            @endauth
                        @endif

                        @if($room->allows_pets)
                            <div class="p-4 bg-[#EFEBE1]/60 border border-[#233D32]/10 rounded-2xl text-xs text-[#233D32] leading-relaxed font-light">
                                🐾 <strong>Pet Policy:</strong> {{ $petPolicy }}
                            </div>
                        @endif

                        <div class="border-t border-[#233D32]/10 pt-4 space-y-2.5 text-xs text-[#595D58]">
                            <div class="flex justify-between">
                                <span>Check-In Time</span>
                                <span class="font-semibold text-[#292D29]">2:00 PM</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Check-Out Time</span>
                                <span class="font-semibold text-[#292D29]">12:00 PM</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Cancellation Policy</span>
                                <span class="font-semibold text-[#292D29]">Standard Resort Policy</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
