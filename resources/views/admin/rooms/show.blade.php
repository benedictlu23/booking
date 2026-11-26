<x-layouts.admin>
    <x-slot:heading>{{ $room->name }}</x-slot:heading>
    <x-slot:subheading>Accommodation Unit #{{ $room->room_number }} · {{ $room->category?->name }}</x-slot:subheading>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            {{-- Photos Gallery --}}
            @php
                $uniquePhotos = $room->photos->unique('path')->values();
            @endphp
            @if($uniquePhotos->isNotEmpty())
                <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                    <div x-data="{ active: 0 }" class="relative aspect-video bg-[#FAF8F4]">
                        @foreach($uniquePhotos as $i => $photo)
                            <img x-show="active === {{ $i }}" src="{{ asset('storage/'.$photo->path) }}"
                                 alt="{{ $room->name }}" class="absolute inset-0 w-full h-full object-cover">
                        @endforeach
                        @if($uniquePhotos->count() > 1)
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                                @foreach($uniquePhotos as $i => $photo)
                                    <button @click="active = {{ $i }}"
                                            :class="active === {{ $i }} ? 'bg-[#A68B61] w-6' : 'bg-white/70 w-2'"
                                            class="h-2 rounded-full transition-all shadow-xs"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Specifications & Details --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Accommodation Folio</span>
                <h3 class="font-serif font-bold text-[#233D32] text-xl border-b border-[#233D32]/10 pb-4 mb-6">Unit Specifications</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] mb-3">Room Information</p>
                        <div class="space-y-2.5 text-xs">
                            <div><span class="text-[#777A72]">Accommodation Type:</span>
                                <span class="ml-1 px-2.5 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider
                                    {{ $room->type === 'cottage' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                                    {{ ucfirst($room->type ?? 'room') }}
                                </span>
                            </div>
                            <div><span class="text-[#777A72]">Category:</span> <span class="font-medium text-[#292D29] ml-1">{{ $room->category?->name }}</span></div>
                            <div><span class="text-[#777A72]">Booking Type:</span> <span class="font-medium text-[#292D29] capitalize ml-1">{{ str_replace('_', ' ', $room->booking_type) }}</span></div>
                            <div><span class="text-[#777A72]">Operational Status:</span>
                                <span class="ml-1 px-2.5 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider
                                    {{ $room->status === 'available' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-[#233D32]/10 text-[#233D32] border border-[#233D32]/20' }}">
                                    {{ ucfirst($room->status) }}
                                </span>
                            </div>
                            <div><span class="text-[#777A72]">Guest Visibility:</span> <span class="font-medium text-[#292D29] ml-1">{{ $room->is_active ? 'Active on Catalog' : 'Hidden' }}</span></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] mb-3">Capacity & Rates</p>
                        <div class="space-y-2.5 text-xs">
                            <div><span class="text-[#777A72]">Standard Capacity:</span> <span class="font-medium text-[#292D29] ml-1">{{ $room->capacity_adults }} Adults, {{ $room->capacity_children }} Children</span></div>
                            <div><span class="text-[#777A72]">Base Nightly Rate:</span> <span class="font-serif font-bold text-base text-[#233D32] ml-1">₱{{ number_format($room->base_price, 2) }}</span></div>
                            @if($room->allows_pets)
                                <div><span class="text-[#777A72]">Pet Accommodation:</span> <span class="font-medium text-[#A68B61] ml-1">₱{{ number_format($room->pet_fee, 2) }}/night</span></div>
                            @endif
                        </div>
                    </div>
                </div>
                @if($room->description)
                    <div class="mt-6 p-4 bg-[#FAF8F4] rounded-xl border border-[#233D32]/10">
                        <p class="text-xs text-[#595D58] font-light leading-relaxed">{{ $room->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Amenities --}}
            @if($room->amenities->isNotEmpty())
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Features</span>
                    <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4">Included Amenities</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($room->amenities as $amenity)
                            <span class="px-3 py-1.5 bg-[#FAF8F4] border border-[#233D32]/10 text-[#233D32] text-xs rounded-xl font-medium">
                                ✓ {{ $amenity->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Scoped Add-on Services --}}
            @if($room->addonServices->isNotEmpty())
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Add-on Services</span>
                    <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4">Configured Add-on Services & Limits</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($room->addonServices as $addon)
                            <div class="p-3.5 bg-[#FAF8F4] border border-[#233D32]/10 rounded-xl flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-xs text-[#292D29]">{{ $addon->name }}</p>
                                    <p class="text-[11px] text-[#777A72]">₱{{ number_format($addon->price, 2) }} / {{ $addon->charge_type ?? 'item' }}</p>
                                </div>
                                <span class="px-2.5 py-1 bg-white border border-[#233D32]/15 text-[#233D32] text-[11px] font-mono font-semibold rounded-lg shadow-2xs">
                                    Max: {{ $addon->pivot->max_quantity ?? 1 }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recent Bookings --}}
            @if($room->bookings->isNotEmpty())
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">History</span>
                    <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4">Recent Stays & Bookings</h3>
                    <div class="space-y-2.5">
                        @foreach($room->bookings as $booking)
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="flex items-center justify-between p-3.5 rounded-xl bg-[#FAF8F4] border border-[#233D32]/10 hover:border-[#233D32]/30 transition-colors">
                                <div>
                                    <p class="font-mono text-xs text-[#A68B61] font-bold">{{ $booking->booking_code }}</p>
                                    <p class="font-serif font-bold text-sm text-[#233D32] mt-0.5">{{ $booking->guest_name }}</p>
                                    <p class="text-[11px] text-[#777A72]">{{ $booking->check_in->format('M d') }} – {{ $booking->check_out->format('M d, Y') }}</p>
                                </div>
                                <span class="font-serif font-bold text-sm text-[#233D32]">₱{{ number_format($booking->total_price, 0) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column Actions --}}
        <div>
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Inventory Control</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4">Unit Actions</h3>
                <div class="space-y-2.5">
                    <a href="{{ route('admin.rooms.edit', $room) }}"
                       class="block w-full py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest text-center transition-all shadow-sm">
                        Edit Accommodation
                    </a>
                    <a href="{{ route('admin.bookings.create', ['room_id' => $room->id]) }}"
                       class="block w-full py-3 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 font-semibold rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                        + Assign Walk-In Booking
                    </a>
                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                          onsubmit="return confirm('Permanently remove this room unit from resort records?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-2.5 border border-rose-300 hover:bg-rose-50 text-rose-700 font-semibold rounded-xl text-xs uppercase tracking-wider transition-all">
                            Delete Room Unit
                        </button>
                    </form>
                    <a href="{{ route('admin.rooms.index') }}"
                       class="block w-full py-2 text-center text-[#777A72] hover:text-[#233D32] text-xs uppercase tracking-wider font-semibold transition-colors mt-2">
                        ← Back to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>