<x-layouts.app>
    <x-slot:title>Reservation {{ $booking->booking_code }} | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-20 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            {{-- Navigation & Title --}}
            <div class="mb-6">
                <a href="{{ route('customer.bookings') }}" class="inline-flex items-center gap-2 text-xs uppercase tracking-wider font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors">
                    <span>←</span>
                    <span>Back to My Bookings</span>
                </a>
                <div class="flex items-center justify-between mt-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32]">Reservation Details</h1>
                    <span class="font-mono text-xs font-semibold text-[#A68B61] bg-[#FAF8F4] px-3 py-1.5 rounded-xl border border-[#233D32]/10">
                        {{ $booking->booking_code }}
                    </span>
                </div>
            </div>

            {{-- Status Banner --}}
            @php
                $banners = [
                    'pending' => ['bg' => 'bg-amber-50/80 border-amber-200/80', 'text' => 'text-amber-900', 'badge' => 'Pending Confirmation', 'msg' => 'Your booking is awaiting resort confirmation. You may settle payment via GCash/BDO or upon arrival.'],
                    'confirmed' => ['bg' => 'bg-emerald-50/80 border-emerald-200/80', 'text' => 'text-emerald-900', 'badge' => 'Confirmed', 'msg' => 'Your reservation is confirmed! We look forward to welcoming you to the countryside.'],
                    'checked_in' => ['bg' => 'bg-[#233D32]/10 border-[#233D32]/20', 'text' => 'text-[#233D32]', 'badge' => 'Currently Checked In', 'msg' => 'Welcome to Agro-Inland Resort! Enjoy your tranquil countryside retreat.'],
                    'checked_out' => ['bg-stone-100 border-stone-200', 'text' => 'text-stone-800', 'badge' => 'Completed', 'msg' => 'Thank you for staying with us. We hope you enjoyed your time with nature.'],
                    'cancelled' => ['bg-rose-50/80 border-rose-200/80', 'text' => 'text-rose-900', 'badge' => 'Cancelled', 'msg' => 'This reservation has been cancelled.'],
                ];
                $banner = $banners[$booking->status] ?? $banners['pending'];
            @endphp
            <div class="p-4 sm:p-5 {{ $banner['bg'] }} border rounded-2xl mb-6 flex items-start gap-3 shadow-2xs">
                <div class="w-6 h-6 rounded-full bg-white/80 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 {{ $banner['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-wider font-bold {{ $banner['text'] }}">{{ $banner['badge'] }}</span>
                    <p class="text-xs font-light {{ $banner['text'] }} mt-0.5 leading-relaxed">{{ $banner['msg'] }}</p>
                </div>
            </div>

            {{-- Main Folio Container --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(35,61,50,0.06)] border border-[#233D32]/10 overflow-hidden">
                {{-- Folio Header --}}
                <div class="bg-[#172A22] text-[#F7F5EF] p-6 sm:p-8 relative overflow-hidden">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/[0.03] rounded-full pointer-events-none"></div>
                    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.22em] text-[#A68B61] font-semibold">Official Folio</span>
                            <p class="text-xl sm:text-2xl font-serif font-bold text-[#F7F5EF] mt-0.5 tracking-wide">{{ $booking->booking_code }}</p>
                            <p class="text-xs text-[#A8B29A] mt-1 font-light">Created {{ $booking->created_at->format('M d, Y · h:i A') }}</p>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-[10px] uppercase tracking-[0.2em] text-[#A8B29A] font-semibold block">Settlement Status</span>
                            <span class="inline-block px-3 py-1 mt-1 rounded-lg text-xs uppercase tracking-wider font-semibold {{ $booking->payment_status === 'paid' ? 'bg-[#A68B61] text-[#172A22]' : 'bg-white/10 text-[#F7F5EF] border border-white/15' }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    {{-- Stay Details Metric Strip --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-[#FAF8F4] border border-[#233D32]/10 rounded-2xl text-xs">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-[#777A72]">Check In</p>
                            <p class="font-serif font-bold text-sm text-[#233D32] mt-1">{{ $booking->check_in->format('D, M d') }}</p>
                            <p class="text-[11px] text-[#777A72]">{{ $booking->check_in->format('Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-[#777A72]">Check Out</p>
                            <p class="font-serif font-bold text-sm text-[#233D32] mt-1">{{ $booking->check_out->format('D, M d') }}</p>
                            <p class="text-[11px] text-[#777A72]">{{ $booking->check_out->format('Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-[#777A72]">Duration</p>
                            <p class="font-serif font-bold text-sm text-[#233D32] mt-1">{{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}</p>
                            <p class="text-[11px] text-[#777A72]">{{ $booking->is_day_pass ? 'Day Pass' : 'Overnight' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-[#777A72]">Guests</p>
                            <p class="font-serif font-bold text-sm text-[#233D32] mt-1">{{ $booking->adults }} Adults{{ $booking->children > 0 ? ', '.$booking->children.' Children' : '' }}</p>
                            @if($booking->pets > 0)
                                <p class="text-[11px] text-[#A68B61] font-medium">🐾 {{ $booking->pets }} Pet(s)</p>
                            @endif
                        </div>
                    </div>

                    {{-- Room Identification Card --}}
                    <div class="flex items-center gap-4 p-4 border border-[#233D32]/10 rounded-2xl bg-white">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-[#FAF8F4] border border-[#233D32]/10 shrink-0 flex items-center justify-center">
                            @if($booking->room?->photos->first())
                                <img src="{{ $booking->room->photos->first()->path }}"
                                     alt="{{ $booking->room->name }}" class="w-full h-full object-cover">
                            @else
                                <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Resort" class="w-10 h-10 object-contain opacity-70">
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] uppercase tracking-[0.16em] font-semibold text-[#A68B61]">{{ $booking->room?->category?->name }}</span>
                            <p class="font-serif font-bold text-base sm:text-lg text-[#233D32]">{{ $booking->room?->name }}</p>
                            <p class="text-xs text-[#777A72]">Room Unit #{{ $booking->room?->room_number }}</p>
                            @if($booking->special_requests)
                                <div class="mt-2 text-xs text-[#233D32] bg-[#FAF8F4] border border-[#233D32]/10 px-3 py-1.5 rounded-lg font-light">
                                    <span class="font-semibold text-[#A68B61]">Special note:</span> {{ $booking->special_requests }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    @if($booking->addons->isNotEmpty())
                        <div class="border-t border-[#233D32]/10 pt-5">
                            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-3">Selected Experiences & Add-ons</span>
                            <div class="space-y-2">
                                @foreach($booking->addons as $addon)
                                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-[#233D32]/5">
                                        <span class="text-[#292D29] font-medium">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Custom Service' }} × {{ $addon->quantity }}</span>
                                        <span class="font-serif font-semibold text-[#233D32]">₱{{ number_format($addon->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price Breakdown --}}
                    <div class="border-t border-[#233D32]/10 pt-5 space-y-2 text-xs">
                        <div class="flex justify-between text-[#777A72]">
                            <span>Accommodation Rate ({{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }})</span>
                            <span class="font-medium text-[#292D29]">₱{{ number_format($booking->base_price, 2) }}</span>
                        </div>
                        @if($booking->addons_total > 0)
                            <div class="flex justify-between text-[#777A72]">
                                <span>Add-on Services</span>
                                <span class="font-medium text-[#292D29]">₱{{ number_format($booking->addons_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->pet_fee_total > 0)
                            <div class="flex justify-between text-[#A68B61]">
                                <span>Pet Accommodation Fee</span>
                                <span class="font-medium">₱{{ number_format($booking->pet_fee_total, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->discount_amount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Applied Privilege / Discount</span>
                                <span class="font-medium">-₱{{ number_format($booking->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-serif font-bold text-base pt-3 border-t border-[#233D32]/10 text-[#233D32]">
                            <span>Total Charges</span>
                            <span class="text-xl">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        @if($booking->amount_paid > 0)
                            <div class="flex justify-between text-xs pt-1.5 text-[#777A72]">
                                <span>Amount Settled</span>
                                <span class="font-semibold text-emerald-700">₱{{ number_format($booking->amount_paid, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs pt-1">
                                <span class="font-semibold text-[#292D29]">Balance Remaining</span>
                                <span class="font-bold {{ $booking->balance_due > 0 ? 'text-[#A68B61]' : 'text-emerald-700' }}">₱{{ number_format($booking->balance_due, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Cancel action --}}
                    @if(in_array($booking->status, ['pending', 'confirmed']))
                        <div class="pt-4 border-t border-[#233D32]/10">
                            <form method="POST" action="{{ route('booking.cancel', $booking) }}"
                                  onsubmit="return confirm('Are you sure you wish to cancel this reservation?')">
                                @csrf
                                <button type="submit"
                                        class="w-full py-3 border border-rose-300 text-rose-700 hover:bg-rose-50 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
                                    Cancel Reservation
                                </button>
                            </form>
                            <p class="text-[11px] text-[#777A72] text-center mt-2 font-light">Cancellations must be requested at least 24 hours prior to check-in.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>