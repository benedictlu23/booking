<x-layouts.app>
    <x-slot:title>Booking {{ $booking->booking_code }}</x-slot:title>

    <div class="pt-24 pb-16 min-h-screen bg-stone-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="pt-4 mb-6">
                <a href="{{ route('customer.bookings') }}" class="text-sm text-stone-500 hover:text-stone-700">← My Bookings</a>
                <h1 class="text-2xl font-black text-stone-900 mt-2">Booking Detail</h1>
            </div>

            {{-- Status Banner --}}
            @php
                $banners = [
                    'pending' => ['bg' => 'bg-amber-50 border-amber-200', 'text' => 'text-amber-800', 'icon' => '⏳', 'msg' => 'Your booking is awaiting confirmation. Please prepare for payment upon arrival.'],
                    'confirmed' => ['bg' => 'bg-blue-50 border-blue-200', 'text' => 'text-blue-800', 'icon' => '✅', 'msg' => "Your booking is confirmed! We look forward to welcoming you."],
                    'checked_in' => ['bg' => 'bg-emerald-50 border-emerald-200', 'text' => 'text-emerald-800', 'icon' => '🏨', 'msg' => 'You are currently checked in. Enjoy your stay!'],
                    'checked_out' => ['bg' => 'bg-stone-50 border-stone-200', 'text' => 'text-stone-700', 'icon' => '👋', 'msg' => 'Thank you for staying with us. We hope to see you again!'],
                    'cancelled' => ['bg' => 'bg-red-50 border-red-200', 'text' => 'text-red-800', 'icon' => '❌', 'msg' => 'This booking has been cancelled.'],
                ];
                $banner = $banners[$booking->status] ?? $banners['pending'];
            @endphp
            <div class="p-4 {{ $banner['bg'] }} border rounded-2xl mb-5 flex items-center gap-3">
                <span class="text-2xl">{{ $banner['icon'] }}</span>
                <p class="text-sm font-medium {{ $banner['text'] }}">{{ $banner['msg'] }}</p>
            </div>

            <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/50 border border-stone-100 overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-200 text-sm">Booking Code</p>
                            <p class="text-2xl font-black font-mono tracking-widest">{{ $booking->booking_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-emerald-200 text-sm">Payment</p>
                            <span class="inline-block px-3 py-1 {{ $booking->payment_status === 'paid' ? 'bg-emerald-400/30' : 'bg-white/20' }} rounded-lg text-sm font-semibold capitalize">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Stay Details --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-stone-50 rounded-2xl text-sm">
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Check In</p>
                            <p class="font-bold text-stone-900 mt-1">{{ $booking->check_in->format('D, M d') }}</p>
                            <p class="text-xs text-stone-500">{{ $booking->check_in->format('Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Check Out</p>
                            <p class="font-bold text-stone-900 mt-1">{{ $booking->check_out->format('D, M d') }}</p>
                            <p class="text-xs text-stone-500">{{ $booking->check_out->format('Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Duration</p>
                            <p class="font-bold text-stone-900 mt-1">{{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Guests</p>
                            <p class="font-bold text-stone-900 mt-1">{{ $booking->adults }}A{{ $booking->children > 0 ? ', '.$booking->children.'C' : '' }}</p>
                            @if($booking->pets > 0) <p class="text-xs text-amber-600">🐾 {{ $booking->pets }} pet(s)</p> @endif
                        </div>
                    </div>

                    {{-- Room --}}
                    <div class="flex items-center gap-4 p-4 border border-stone-100 rounded-2xl">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-stone-100 shrink-0">
                            @if($booking->room?->photos->first())
                                <img src="{{ asset('storage/'.$booking->room->photos->first()->path) }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl">🏡</div>
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-stone-900">{{ $booking->room?->name }}</p>
                            <p class="text-sm text-stone-500">Room {{ $booking->room?->room_number }} · {{ $booking->room?->category?->name }}</p>
                            @if($booking->special_requests)
                                <p class="text-xs text-amber-700 mt-1 bg-amber-50 px-2 py-1 rounded">📝 {{ $booking->special_requests }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    @if($booking->addons->isNotEmpty())
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-3">Add-ons</p>
                            <div class="space-y-2">
                                @foreach($booking->addons as $addon)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-stone-700">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Custom Service' }} × {{ $addon->quantity }}</span>
                                        <span class="font-medium">₱{{ number_format($addon->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price Breakdown --}}
                    <div class="border-t border-stone-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-stone-500">
                            <span>Base Price ({{ $booking->nights }} nights)</span>
                            <span>₱{{ number_format($booking->base_price, 2) }}</span>
                        </div>
                        @if($booking->addons_total > 0)
                            <div class="flex justify-between text-stone-500"><span>Add-ons</span><span>₱{{ number_format($booking->addons_total, 2) }}</span></div>
                        @endif
                        @if($booking->pet_fee_total > 0)
                            <div class="flex justify-between text-amber-600"><span>🐾 Pet Fee</span><span>₱{{ number_format($booking->pet_fee_total, 2) }}</span></div>
                        @endif
                        @if($booking->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600"><span>Discount</span><span>-₱{{ number_format($booking->discount_amount, 2) }}</span></div>
                        @endif
                        <div class="flex justify-between font-black text-lg pt-2 border-t border-stone-100">
                            <span>Total</span>
                            <span class="text-emerald-600">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>

                    {{-- Cancel action --}}
                    @if(in_array($booking->status, ['pending', 'confirmed']))
                        <div class="pt-2">
                            <form method="POST" action="{{ route('booking.cancel', $booking) }}"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                @csrf
                                <button type="submit"
                                        class="w-full py-3 border-2 border-red-200 text-red-600 font-semibold rounded-2xl hover:bg-red-50 text-sm transition-colors">
                                    Cancel Booking
                                </button>
                            </form>
                            <p class="text-xs text-stone-400 text-center mt-2">Please cancel at least 24 hours before check-in.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
