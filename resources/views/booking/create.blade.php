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
                    {{-- Stay Dates --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-bold text-stone-900">Your Stay</h2>
                            <div x-show="checkingAvailability" class="text-xs text-stone-400 flex items-center gap-1">
                                <span class="animate-spin">⏳</span> Checking dates...
                            </div>
                            <div x-show="!checkingAvailability && availabilityChecked">
                                <span x-show="isAvailable" class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg flex items-center gap-1">
                                    ✓ Available
                                </span>
                                <span x-show="!isAvailable" class="px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-semibold rounded-lg flex items-center gap-1">
                                    ⚠️ Not Available
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check-In</label>
                                <input type="date" name="check_in" x-model="checkIn" @change="checkDateAvailability()"
                                       value="{{ old('check_in', $checkIn) }}" min="{{ today()->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check-Out</label>
                                <input type="date" name="check_out" x-model="checkOut" @change="checkDateAvailability()"
                                       value="{{ old('check_out', $checkOut) }}" min="{{ today()->addDay()->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Booking Type</label>
                                <select name="booking_type" x-model="bookingType" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="overnight" {{ old('booking_type') === 'overnight' ? 'selected' : '' }}>Overnight Stay</option>
                                    <option value="day_pass" {{ old('booking_type') === 'day_pass' ? 'selected' : '' }}>Day Pass</option>
                                </select>
                            </div>
                        </div>
                        <div x-show="!isAvailable && availabilityChecked" class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700">
                            ⚠️ This room is not available for the selected dates. Please select other dates.
                        </div>
                    </div>

                    {{-- Guests --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Guests</h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                                <input type="number" name="adults" x-model.number="adults" min="1" max="{{ $room->capacity_adults }}" required
                                       value="{{ old('adults', 1) }}"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <p class="text-xs text-stone-400 mt-1">Max {{ $room->capacity_adults }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Children</label>
                                <input type="number" name="children" x-model.number="children" min="0" max="{{ $room->capacity_children }}"
                                       value="{{ old('children', 0) }}"
                                       class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            @if($room->allows_pets)
                                <div>
                                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Pets 🐾</label>
                                    <input type="number" name="pets" x-model.number="pets" min="0"
                                           value="{{ old('pets', 0) }}"
                                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <p class="text-xs text-amber-600 mt-1">+₱{{ number_format($room->pet_fee, 0) }}/night each</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Add-ons (Strictly Optional, zero-quantity safe) --}}
                    @if($addons->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                            <h2 class="font-bold text-stone-900 mb-1">Enhance Your Stay</h2>
                            <p class="text-sm text-stone-500 mb-4">Add optional extras to your booking (optional)</p>
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
                                            <input type="hidden" name="addons[{{ $loop->index }}][id]" value="{{ $addon->id }}">
                                            <input type="number" name="addons[{{ $loop->index }}][quantity]"
                                                   min="0" max="10" value="{{ old("addons.{$loop->index}.quantity", 0) }}" placeholder="0"
                                                   @input="updateAddons({{ $addon->id }}, $event.target.value, {{ $addon->price }})"
                                                   class="w-full px-2 py-2 border border-stone-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Payment Method Option --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Payment Method</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer hover:border-emerald-400 transition-colors"
                                   :class="paymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200'">
                                <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="font-semibold text-xs text-stone-900">Cash on Arrival</p>
                                    <p class="text-[11px] text-stone-500">Pay at Front Desk</p>
                                </div>
                            </label>
                            <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer hover:border-emerald-400 transition-colors"
                                   :class="paymentMethod === 'gcash' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200'">
                                <input type="radio" name="payment_method" value="gcash" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="font-semibold text-xs text-stone-900">GCash</p>
                                    <p class="text-[11px] text-stone-500">Online transfer</p>
                                </div>
                            </label>
                            <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer hover:border-emerald-400 transition-colors"
                                   :class="paymentMethod === 'bank_transfer' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200'">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="font-semibold text-xs text-stone-900">Bank Transfer</p>
                                    <p class="text-[11px] text-stone-500">BDO Account</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Discount Code --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                        <h2 class="font-bold text-stone-900 mb-4">Discount Code</h2>
                        <div class="flex gap-3">
                            <input type="text" name="discount_code" x-model="discountCode"
                                   value="{{ old('discount_code') }}" placeholder="Enter promo code..."
                                   class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase">
                            <button type="button" @click="applyDiscount()" :disabled="applyingDiscount"
                                    class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-xl transition-colors">
                                <span x-show="!applyingDiscount">Apply</span>
                                <span x-show="applyingDiscount">Applying...</span>
                            </button>
                        </div>
                        <template x-if="discountStatusMessage">
                            <p class="mt-2 text-xs font-semibold" :class="discountValid ? 'text-emerald-600' : 'text-red-600'" x-text="discountStatusMessage"></p>
                        </template>
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
                            <template x-if="discountAmount > 0">
                                <div class="flex justify-between text-emerald-600">
                                    <span>Discount</span>
                                    <span>-₱<span x-text="formatMoney(discountAmount)"></span></span>
                                </div>
                            </template>
                            <div class="border-t border-stone-100 pt-2 flex justify-between font-black text-base">
                                <span class="text-stone-900">Total</span>
                                <span class="text-emerald-600">₱<span x-text="formatMoney(total)"></span></span>
                            </div>
                        </div>

                        <button type="submit" :disabled="!isAvailable || checkingAvailability"
                                :class="(!isAvailable || checkingAvailability) ? 'opacity-50 cursor-not-allowed bg-stone-400' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 hover:-translate-y-0.5'"
                                class="w-full py-3.5 text-white font-bold rounded-2xl transition-all shadow-lg shadow-emerald-200 text-sm">
                            <span x-show="!checkingAvailability">Confirm Booking →</span>
                            <span x-show="checkingAvailability">Checking Availability...</span>
                        </button>
                        <p class="text-xs text-stone-400 text-center mt-2">Payment due at check-in or via proof upload. Free cancellation 24h before.</p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bookingForm() {
        return {
            checkIn: '{{ old('check_in', $checkIn) }}',
            checkOut: '{{ old('check_out', $checkOut) }}',
            bookingType: '{{ old('booking_type', 'overnight') }}',
            paymentMethod: '{{ old('payment_method', 'cash') }}',
            adults: {{ old('adults', 1) }},
            children: {{ old('children', 0) }},
            pets: {{ old('pets', 0) }},
            addons: {},
            basePrice: {{ $room->base_price }},
            petFee: {{ $room->pet_fee }},
            discountCode: '{{ old('discount_code', '') }}',
            discountAmount: 0,
            discountValid: false,
            discountStatusMessage: '',
            applyingDiscount: false,
            isAvailable: true,
            checkingAvailability: false,
            availabilityChecked: false,

            init() {
                this.checkDateAvailability();
                if (this.discountCode) {
                    this.applyDiscount();
                }
            },

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
            get total() {
                return Math.max(0, this.baseTotal + this.petTotal + this.addonsTotal - this.discountAmount);
            },

            updateAddons(id, qty, price) {
                const q = parseInt(qty) || 0;
                if (q > 0) {
                    this.addons[id] = { qty: q, price: parseFloat(price) };
                } else {
                    delete this.addons[id];
                }
            },

            async checkDateAvailability() {
                if (!this.checkIn || !this.checkOut) return;
                this.checkingAvailability = true;
                try {
                    const res = await fetch(`{{ route('rooms.availability', $room) }}?check_in=${this.checkIn}&check_out=${this.checkOut}`);
                    const data = await res.json();
                    this.isAvailable = data.available;
                    this.availabilityChecked = true;
                } catch (e) {
                    this.isAvailable = true;
                } finally {
                    this.checkingAvailability = false;
                }
            },

            async applyDiscount() {
                if (!this.discountCode) {
                    this.discountAmount = 0;
                    this.discountStatusMessage = '';
                    return;
                }
                this.applyingDiscount = true;
                try {
                    const subtotal = this.baseTotal + this.petTotal + this.addonsTotal;
                    const res = await fetch(`{{ route('discounts.validate') }}?code=${encodeURIComponent(this.discountCode)}&amount=${subtotal}`);
                    const data = await res.json();
                    if (data.valid) {
                        this.discountValid = true;
                        this.discountAmount = data.discount_amount;
                        this.discountStatusMessage = data.message;
                    } else {
                        this.discountValid = false;
                        this.discountAmount = 0;
                        this.discountStatusMessage = data.message;
                    }
                } catch (e) {
                    this.discountValid = false;
                    this.discountAmount = 0;
                    this.discountStatusMessage = 'Error validating promo code.';
                } finally {
                    this.applyingDiscount = false;
                }
            },

            formatMoney(n) {
                return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2 }).format(n);
            }
        };
    }
    </script>
</x-layouts.app>
