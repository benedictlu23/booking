<x-layouts.app>
    <x-slot:title>Search Rooms</x-slot:title>

    <div class="pt-24 pb-20 min-h-screen bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-black text-stone-900">Find Your Perfect Room</h1>
                <p class="text-stone-500 mt-2">{{ $rooms->count() }} room(s) match your search</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                {{-- Filters Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 sticky top-24">
                        <h2 class="font-bold text-stone-900 mb-5">Refine Search</h2>
                        <form method="GET" action="{{ route('rooms.search') }}" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check In</label>
                                <input type="date" name="check_in" value="{{ $checkIn }}" min="{{ today()->format('Y-m-d') }}"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check Out</label>
                                <input type="date" name="check_out" value="{{ $checkOut }}" min="{{ today()->addDay()->format('Y-m-d') }}"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                                <input type="number" name="adults" value="{{ $adults }}" min="1"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Children</label>
                                <input type="number" name="children" value="{{ $children }}" min="0"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div x-data="{ pets: {{ $pets }} }">
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Pets 🐾</label>
                                <input type="number" name="pets" x-model="pets" min="0"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <p x-show="pets > 0" class="mt-2 text-xs text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200">
                                    🐾 {{ $petPolicy }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Type</label>
                                <select name="booking_type" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="overnight" {{ $bookingType === 'overnight' ? 'selected' : '' }}>Overnight</option>
                                    <option value="day_pass" {{ $bookingType === 'day_pass' ? 'selected' : '' }}>Day Pass</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="category_id" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Min Price</label>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="₱"
                                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Max Price</label>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="₱"
                                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors">
                                Search
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Room Results --}}
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @forelse($rooms as $room)
                            <div class="group bg-white rounded-2xl overflow-hidden shadow-md shadow-stone-200/60 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                {{-- Room Image --}}
                                <div class="relative h-52 overflow-hidden bg-stone-100">
                                    @if($room->photos->isNotEmpty())
                                        <img src="{{ asset('storage/'.$room->photos->first()->path) }}"
                                             alt="{{ $room->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                            <span class="text-5xl">🌿</span>
                                        </div>
                                    @endif
                                    <div class="absolute top-3 left-3 flex gap-2">
                                        <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold text-stone-700 rounded-lg">
                                            {{ $room->category->name }}
                                        </span>
                                        @if($room->allows_pets)
                                            <span class="px-2.5 py-1 bg-amber-100/90 text-amber-800 text-xs font-semibold rounded-lg">🐾 Pet OK</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-bold text-stone-900">{{ $room->name }}</h3>
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

                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-stone-500">👥 Up to {{ $room->capacity_adults }} adults</span>
                                        <a href="{{ route('rooms.show', array_merge(request()->query(), ['room' => $room->id, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children, 'pets' => $pets])) }}"
                                           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-20 text-stone-400">
                                <div class="text-6xl mb-4">🌿</div>
                                <h3 class="text-xl font-bold text-stone-600 mb-2">No rooms available</h3>
                                <p class="text-sm">Try different dates or adjust your filters.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
