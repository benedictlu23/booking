<x-layouts.app>
    <x-slot:title>Book {{ $room->name }}</x-slot:title>

    <div class="pt-24 pb-20 min-h-screen bg-stone-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 pt-4">
                <a href="{{ route('rooms.show', $room) }}" class="text-sm text-stone-500 hover:text-stone-700">← Back to Room Details</a>
                <h1 class="text-2xl font-black text-stone-900 mt-2">Complete Your Booking</h1>
            </div>

            <form action="{{ route('booking.store') }}" method="POST" x-data="bookingForm()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">

                {{-- Left: Form --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Stay Dates --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Your Stay</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check-In</label>
                                <input type="date" name="check_in" x-model="checkIn"
                                       value="{{ $checkIn }}" min="{{ today()->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check-Out</label>
                                <input type="date" name="check_out" x-model="checkOut"
                                       value="{{ $checkOut }}" min="{{ today()->addDay()->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Booking Type</label>
                                <select name="booking_type" x-model="bookingType" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="overnight">Overnight Stay</option>
                                    <option value="day_pass">Day Pass</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Guests --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Guests</h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                                <input type="number" name="adults" x-model.number="adults" min="1" max="{{ $room->capacity_adults }}" required
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <p class="text-xs text-stone-400 mt-1">Max {{ $room->capacity_adults }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Children</label>
                                <input type="number" name="children" x-model.number="children" min="0" max="{{ $room->capacity_children }}"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            @if($room->allows_pets)
                                <div>
                                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Pets 🐾</label>
                                    <input type="number" name="pets" x-model.number="pets" min="0"
                                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <p class="text-xs text-amber-600 mt-1">+₱{{ number_format($room->pet_fee, 0) }}/night each</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    @if($addons->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                            <h2 class="font-bold text-stone-900 mb-1">Enhance Your Stay</h2>
                            <p class="text-sm text-stone-500 mb-4">Add optional extras to your booking</p>
                            <div class="space-y-3">
                                @foreach($addons as $addon)
                                    <div class="flex items-center gap-4 p-3 border border-stone-100 rounded-xl hover:border-emerald-200 transition-colors">
                                        <span class="text-2xl">{{ $addon->icon ?? '✨' }}</span>
                                        <div class="flex-1">
                                            <p class="font-semibold text-stone-900 text-sm">{{ $addon->name }}</p>
                                            @if($addon->description)
                                                <p class="text-xs text-stone-500">{{ $addon->description }}</p>
                                            @endif
                                            <p class="text-xs text-emerald-600 font-semibold mt-0.5">
                                                ₱{{ number_format($addon->price, 2) }} / {{ str_replace('_', ' ', $addon->pricing_type) }}
                                            </p>
                                        </div>
                                        <div class="shrink-0 w-20">
                                            <input type="number" name="addons[{{ $loop->index }}][id]" value="{{ $addon->id }}" hidden>
                                            <input type="number" name="addons[{{ $loop->index }}][quantity]"
                                                   min="0" max="10" value="0" placeholder="Qty"
                                                   @input="updateAddons({{ $addon->id }}, $event.target.value, {{ $addon->price }})"
                                                   class="w-full px-2 py-2 border border-stone-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Discount Code --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Discount Code</h2>
                        <div class="flex gap-3">
                            <input type="text" name="discount_code" placeholder="Enter promo code..."
                                   class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase">
                            <button type="button" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-xl transition-colors">
                                Apply
                            </button>
                        </div>
                        @error('discount_code')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Special Requests --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Special Requests</h2>
                        <textarea name="special_requests" rows="3" placeholder="Any special requests or notes? (optional)"
                                  class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('special_requests') }}</textarea>
                    </div>

                    @if($errors->any())
                        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl">
                            @foreach($errors->all() as $error)
                                <p class="text-sm text-red-700 mb-1">• {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right: Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-xl shadow-stone-200/50 border border-stone-100 p-6 sticky top-24">
                        <h2 class="font-bold text-stone-900 mb-4">Booking Summary</h2>

                        <div class="mb-4 p-3 bg-stone-50 rounded-xl">
                            <p class="font-semibold text-stone-900 text-sm">{{ $room->name }}</p>
                            <p class="text-xs text-stone-500">Room {{ $room->room_number }} · {{ $room->category->name }}</p>
                        </div>

                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between text-stone-600">
                                <span>₱{{ number_format($room->base_price, 0) }} × <span x-text="nights"></span> night<span x-show="nights !== 1">s</span></span>
                                <span>₱<span x-text="formatMoney(baseTotal)"></span></span>
                            </div>
                            <template x-if="petTotal > 0">
                                <div class="flex justify-between text-amber-700">
                                    <span>Pet fee × <span x-text="nights"></span> night<span x-show="nights !== 1">s</span></span>
                                    <span>₱<span x-text="formatMoney(petTotal)"></span></span>
                                </div>
                            </template>
                            <template x-if="addonsTotal > 0">
                                <div class="flex justify-between text-stone-600">
                                    <span>Add-ons</span>
                                    <span>₱<span x-text="formatMoney(addonsTotal)"></span></span>
                                </div>
                            </template>
                            <div class="border-t border-stone-100 pt-2 flex justify-between font-black text-base">
                                <span class="text-stone-900">Total</span>
                                <span class="text-emerald-600">₱<span x-text="formatMoney(total)"></span></span>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-emerald-200 hover:-translate-y-0.5 text-sm">
                            Confirm Booking →
                        </button>
                        <p class="text-xs text-stone-400 text-center mt-2">Payment due at check-in. Free cancellation 24h before.</p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bookingForm() {
        return {
            checkIn: '{{ $checkIn }}',
            checkOut: '{{ $checkOut }}',
            bookingType: 'overnight',
            adults: 1,
            children: 0,
            pets: 0,
            addons: {},
            basePrice: {{ $room->base_price }},
            petFee: {{ $room->pet_fee }},

            get nights() {
                const d1 = new Date(this.checkIn);
                const d2 = new Date(this.checkOut);
                const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                return Math.max(1, diff || 1);
            },
            get baseTotal() { return this.basePrice * this.nights; },
            get petTotal() { return this.pets > 0 ? this.petFee * this.pets * this.nights : 0; },
            get addonsTotal() {
                return Object.values(this.addons).reduce((sum, a) => sum + (a.price * a.qty), 0);
            },
            get total() { return this.baseTotal + this.petTotal + this.addonsTotal; },

            updateAddons(id, qty, price) {
                if (qty > 0) {
                    this.addons[id] = { qty: parseInt(qty), price: parseFloat(price) };
                } else {
                    delete this.addons[id];
                }
            },
            formatMoney(n) {
                return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2 }).format(n);
            }
        };
    }
    </script>
</x-layouts.app>
