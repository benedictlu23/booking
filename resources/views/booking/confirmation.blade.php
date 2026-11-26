<x-layouts.app>
    <x-slot:title>Reservation Confirmed | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-24 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">

            {{-- Success Banner --}}
            <div class="text-center mb-10 pt-4">
                <div class="w-16 h-16 rounded-full bg-[#EFEBE1] border border-[#A68B61]/40 flex items-center justify-center mx-auto mb-4 text-[#233D32] shadow-xs">
                    <svg class="w-8 h-8 text-[#233D32]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="text-xs uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Reservation Received</span>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#233D32] mt-1">We Look Forward to Welcoming You</h1>
                <p class="text-xs text-[#777A72] mt-2 font-light max-w-md mx-auto">
                    Your countryside retreat has been reserved. A copy of your reservation folio is detailed below.
                </p>
            </div>

            {{-- Reservation Folio Card --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(35,61,50,0.08)] border border-[#233D32]/10 overflow-hidden">

                {{-- Header --}}
                <div class="bg-[#172A22] p-7 text-[#F7F5EF] border-b border-[#233D32]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Reservation Reference</span>
                            <p class="text-2xl font-serif font-bold tracking-wider mt-0.5">{{ $booking->booking_code }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block">Booking Status</span>
                            <span class="inline-block mt-1 px-3 py-1 bg-white/10 text-[#F7F5EF] rounded-lg text-xs font-semibold capitalize border border-white/15">
                                {{ str_replace('_', ' ', $booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-7 space-y-6">

                    {{-- Stay Schedule Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/10 text-xs">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#777A72]">Check In</p>
                            <p class="font-bold text-[#233D32] text-sm mt-0.5">{{ $booking->check_in->format('M d, Y') }}</p>
                            <p class="text-[10px] text-[#777A72] mt-0.5">From 2:00 PM</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#777A72]">Check Out</p>
                            <p class="font-bold text-[#233D32] text-sm mt-0.5">{{ $booking->check_out->format('M d, Y') }}</p>
                            <p class="text-[10px] text-[#777A72] mt-0.5">By 12:00 PM</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#777A72]">Duration</p>
                            <p class="font-bold text-[#233D32] text-sm mt-0.5">{{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}</p>
                            <p class="text-[10px] text-[#777A72] mt-0.5 capitalize">{{ str_replace('_', ' ', $booking->booking_type) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#777A72]">Guests</p>
                            <p class="font-bold text-[#233D32] text-sm mt-0.5">
                                {{ $booking->adults }} {{ Str::plural('Adult', $booking->adults) }}
                                @if($booking->children > 0)
                                    , {{ $booking->children }} {{ Str::plural('Child', $booking->children) }}
                                @endif
                            </p>
                            @if($booking->pets > 0)
                                <p class="text-[10px] text-[#A68B61] mt-0.5 font-medium">🐾 {{ $booking->pets }} {{ Str::plural('Pet', $booking->pets) }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Primary Guest Details --}}
                    <div class="p-4 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/10 text-xs">
                        <span class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#A68B61] block mb-1">Primary Guest Details</span>
                        <p class="text-sm font-serif font-bold text-[#233D32]">{{ $booking->guest_name }}</p>
                        <p class="text-[#777A72] mt-0.5">{{ $booking->guest_email }} · <span class="font-mono text-[#292D29] font-medium">{{ $booking->guest_phone }}</span></p>
                    </div>

                    {{-- Room Details --}}
                    <div class="flex items-center gap-4 p-4 border border-[#233D32]/10 rounded-2xl bg-white">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-[#FAF8F4] shrink-0 border border-[#233D32]/10">
                            <img src="{{ $booking->room?->photos->first()->path ?? asset('storage/assets/AIR-LOGO.png') }}"
                                 alt="{{ $booking->room?->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 bg-[#EFEBE1] text-[#233D32] text-[10px] font-semibold uppercase tracking-wider rounded">
                                    {{ $booking->room?->category?->name }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded border {{ $booking->room?->type === 'cottage' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300' }}">
                                    {{ ucfirst($booking->room?->type ?? 'room') }}
                                </span>
                            </div>
                            <p class="font-serif font-bold text-[#233D32] text-base mt-1">{{ $booking->room?->name }}</p>
                            <p class="text-xs text-[#777A72] font-mono">Unit {{ $booking->room?->room_number }}</p>
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    @if($booking->addons->isNotEmpty())
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.14em] font-semibold text-[#777A72] mb-3">Add-On Services</p>
                            <div class="space-y-2">
                                @foreach($booking->addons as $addon)
                                    <div class="flex justify-between text-xs p-3 rounded-xl bg-[#FAF8F4] border border-[#233D32]/5">
                                        <span class="text-[#292D29] font-medium">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Service' }} × {{ $addon->quantity }}</span>
                                        <span class="font-semibold text-[#233D32]">₱{{ number_format($addon->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price Breakdown --}}
                    <div class="border-t border-[#233D32]/10 pt-5 space-y-2.5 text-xs text-[#595D58]">
                        <div class="flex justify-between">
                            <span>Base Rate ({{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }})</span>
                            <span class="font-medium text-[#292D29]">₱{{ number_format($booking->base_price, 2) }}</span>
                        </div>
                        @if($booking->addons_total > 0)
                            <div class="flex justify-between">
                                <span>Add-ons Total</span>
                                <span class="font-medium text-[#292D29]">₱{{ number_format($booking->addons_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->pet_fee_total > 0)
                            <div class="flex justify-between text-[#A68B61]">
                                <span>Pet Fee</span>
                                <span class="font-medium">₱{{ number_format($booking->pet_fee_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->discount_amount > 0)
                            <div class="flex justify-between text-[#233D32] font-medium bg-[#EFEBE1] px-2.5 py-1.5 rounded-lg border border-[#233D32]/10">
                                <span>Applied Promotion</span>
                                <span>-₱{{ number_format($booking->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-serif font-bold text-xl text-[#233D32] pt-3 border-t border-[#233D32]/10">
                            <span>Total Charges</span>
                            <span>₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        @if($booking->amount_paid > 0)
                            <div class="flex justify-between text-xs pt-1">
                                <span class="text-[#777A72]">Amount Paid:</span>
                                <span class="font-bold text-[#233D32]">₱{{ number_format($booking->amount_paid, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs pt-1">
                                <span class="text-[#777A72]">Remaining Balance:</span>
                                <span class="font-bold {{ $booking->balance_due > 0 ? 'text-rose-700' : 'text-[#233D32]' }}">₱{{ number_format($booking->balance_due, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-[#777A72]">Payment Method / Status:</span>
                            <span class="font-bold uppercase tracking-wider text-[11px] {{ $booking->payment_status === 'paid' ? 'text-[#233D32]' : ($booking->payment_status === 'partial' ? 'text-[#A68B61]' : 'text-amber-800') }}">
                                {{ $booking->payment_method }} · {{ $booking->payment_status }}
                            </span>
                        </div>
                    </div>

                    {{-- Payment Proof Component --}}
                    <div class="border-t border-[#233D32]/10 pt-5">
                        <livewire:booking.payment-proof :booking="$booking" />
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <a href="{{ route('customer.bookings.show', $booking) }}"
                   class="flex-1 py-4 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold text-center text-xs uppercase tracking-widest rounded-xl transition-all shadow-xs">
                    View My Reservation Details
                </a>
                <a href="{{ route('rooms.search') }}"
                   class="flex-1 py-4 bg-[#EFEBE1] hover:bg-[#E5E0D4] text-[#233D32] font-semibold text-center text-xs uppercase tracking-widest rounded-xl transition-all border border-[#233D32]/15">
                    Browse More Accommodations
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
