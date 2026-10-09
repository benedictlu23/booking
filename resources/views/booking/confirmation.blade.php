<x-layouts.app>
    <x-slot:title>Booking Confirmed</x-slot:title>

    <div class="pt-24 pb-20 min-h-screen bg-gradient-to-br from-emerald-50 via-stone-50 to-teal-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">

            {{-- Success Banner --}}
            <div class="text-center mb-8 pt-8">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-stone-900">Booking Confirmed! 🎉</h1>
                <p class="text-stone-500 mt-2">Your reservation has been received. We'll see you soon!</p>
            </div>

            {{-- Booking Card --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/60 border border-stone-100 overflow-hidden">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-200 text-sm font-medium">Booking Code</p>
                            <p class="text-2xl font-black font-mono tracking-widest">{{ $booking->booking_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-emerald-200 text-sm">Status</p>
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-lg text-sm font-semibold capitalize">
                                {{ str_replace('_', ' ', $booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Stay Info --}}
                    <div class="grid grid-cols-2 gap-4 p-4 bg-stone-50 rounded-2xl">
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Check In</p>
                            <p class="font-bold text-stone-900 text-lg">{{ $booking->check_in->format('D, M d Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Check Out</p>
                            <p class="font-bold text-stone-900 text-lg">{{ $booking->check_out->format('D, M d Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Nights</p>
                            <p class="font-bold text-stone-900">{{ $booking->nights }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Guests</p>
                            <p class="font-bold text-stone-900">
                                {{ $booking->adults }} {{ Str::plural('Adult', $booking->adults) }}
                                @if($booking->children > 0)
                                    , {{ $booking->children }} {{ Str::plural('Child', $booking->children) }}
                                @endif
                                @if($booking->pets > 0)
                                    , 🐾 {{ $booking->pets }} {{ Str::plural('Pet', $booking->pets) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Room --}}
                    <div class="flex items-center gap-4 p-4 border border-stone-100 rounded-2xl">
                        <div class="w-14 h-14 bg-emerald-50 rounded-xl flex items-center justify-center text-2xl">🏡</div>
                        <div>
                            <p class="font-bold text-stone-900">{{ $booking->room?->name }}</p>
                            <p class="text-sm text-stone-500">Room {{ $booking->room?->room_number }} · {{ $booking->room?->category?->name }}</p>
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    @if($booking->addons->isNotEmpty())
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-3">Add-ons</p>
                            <div class="space-y-2">
                                @foreach($booking->addons as $addon)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-stone-600">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Custom Service' }} × {{ $addon->quantity }}</span>
                                        <span class="font-medium text-stone-900">₱{{ number_format($addon->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price Breakdown --}}
                    <div class="border-t border-stone-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-stone-500">
                            <span>Base Price ({{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }})</span>
                            <span>₱{{ number_format($booking->base_price, 2) }}</span>
                        </div>
                        @if($booking->addons_total > 0)
                            <div class="flex justify-between text-stone-500">
                                <span>Add-ons</span>
                                <span>₱{{ number_format($booking->addons_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->pet_fee_total > 0)
                            <div class="flex justify-between text-stone-500">
                                <span>Pet Fee</span>
                                <span>₱{{ number_format($booking->pet_fee_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600">
                                <span>Discount</span>
                                <span>-₱{{ number_format($booking->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-black text-lg pt-2 border-t border-stone-100">
                            <span class="text-stone-900">Total Amount Due</span>
                            <span class="text-emerald-600">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-stone-500">Payment Status:</span>
                            <span class="font-bold uppercase {{ $booking->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $booking->payment_status }}</span>
                        </div>
                    </div>

                    {{-- Manual Payment Flow & Proof Upload --}}
                    @if($booking->payment_proof)
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🧾</span>
                                <div>
                                    <p class="font-bold text-emerald-900 text-sm">Payment Proof Submitted</p>
                                    <p class="text-xs text-emerald-700">Method: <span class="capitalize">{{ $booking->payment_method }}</span> · Our staff is reviewing your payment.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5 bg-stone-50 rounded-2xl border border-stone-200 space-y-3" x-data="{ method: 'gcash' }">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-stone-900 text-sm">💳 Pay Online / Manual Transfer (Optional)</h3>
                                <span class="text-xs text-stone-400">or pay at check-in</span>
                            </div>
                            <p class="text-xs text-stone-600">Upload your proof of payment (screenshot/receipt) to confirm your booking immediately.</p>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2.5 bg-white rounded-xl border border-stone-200">
                                    <p class="font-bold text-stone-900">GCash</p>
                                    <p class="text-stone-500">0912-345-6789</p>
                                    <p class="text-stone-400">Agro-Inland Resort</p>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-stone-200">
                                    <p class="font-bold text-stone-900">BDO Unibank</p>
                                    <p class="text-stone-500">0012-3456-7890</p>
                                    <p class="text-stone-400">Agro-Inland Corp.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('booking.payment-proof', $booking) }}" enctype="multipart/form-data" class="space-y-3 pt-2">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-stone-600 mb-1">Payment Method</label>
                                    <select name="payment_method" x-model="method" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="gcash">GCash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash on Arrival</option>
                                    </select>
                                </div>
                                <div x-show="method !== 'cash'">
                                    <label class="block text-xs font-semibold text-stone-600 mb-1">Proof of Payment Image</label>
                                    <input type="file" name="payment_proof" accept="image/*"
                                           class="w-full text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
                                </div>
                                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                                    Submit Payment Proof
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <a href="{{ route('customer.bookings.show', $booking) }}"
                   class="flex-1 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center rounded-2xl transition-colors">
                    View My Booking
                </a>
                <a href="{{ route('rooms.search') }}"
                   class="flex-1 py-3.5 border-2 border-stone-200 hover:border-stone-300 text-stone-700 font-semibold text-center rounded-2xl transition-colors">
                    Browse More Rooms
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
