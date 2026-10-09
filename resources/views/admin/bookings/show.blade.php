<x-layouts.admin>
    <x-slot:heading>Booking Detail</x-slot:heading>
    <x-slot:subheading>{{ $booking->booking_code }}</x-slot:subheading>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Main Info --}}
        <div class="xl:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <p class="font-mono text-2xl font-black text-stone-900">{{ $booking->booking_code }}</p>
                        <p class="text-stone-500 text-sm mt-1">Created {{ $booking->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'checked_in' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'checked_out' => 'bg-stone-100 text-stone-800 border-stone-200',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                        @endphp
                        <span class="px-3 py-1.5 rounded-xl text-sm font-semibold border {{ $statusColors[$booking->status] ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl text-sm font-semibold border {{ $booking->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                        @if($booking->is_walk_in)
                            <span class="px-3 py-1.5 rounded-xl text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-200">Walk-In</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-3">Guest Information</p>
                        <div class="space-y-2">
                            <div><span class="text-stone-500 text-sm">Name:</span> <span class="font-semibold text-stone-900 text-sm">{{ $booking->guest_name }}</span></div>
                            @if($booking->guest_email)
                                <div><span class="text-stone-500 text-sm">Email:</span> <span class="font-medium text-stone-700 text-sm">{{ $booking->guest_email }}</span></div>
                            @endif
                            @if($booking->guest_phone)
                                <div><span class="text-stone-500 text-sm">Phone:</span> <span class="font-medium text-stone-700 text-sm">{{ $booking->guest_phone }}</span></div>
                            @endif
                            @if($booking->user)
                                <div class="flex items-center gap-2 mt-2">
                                    @if($booking->user->avatar)
                                        <img src="{{ $booking->user->avatar }}" class="w-6 h-6 rounded-full">
                                    @endif
                                    <span class="text-xs text-emerald-600 font-medium">Registered · {{ $booking->user->visit_count }} visits</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-3">Stay Details</p>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-stone-500">Check-In:</span> <span class="font-semibold">{{ $booking->check_in->format('D, M d Y') }}</span></div>
                            <div><span class="text-stone-500">Check-Out:</span> <span class="font-semibold">{{ $booking->check_out->format('D, M d Y') }}</span></div>
                            <div><span class="text-stone-500">Nights:</span> <span class="font-semibold">{{ $booking->nights }}</span></div>
                            <div><span class="text-stone-500">Type:</span> <span class="font-semibold capitalize">{{ str_replace('_', ' ', $booking->booking_type) }}</span></div>
                            <div><span class="text-stone-500">Guests:</span> <span class="font-semibold">{{ $booking->adults }} Adults@if($booking->children > 0), {{ $booking->children }} Children@endif@if($booking->pets > 0), 🐾 {{ $booking->pets }} Pets@endif</span></div>
                        </div>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1">Special Requests</p>
                        <p class="text-sm text-amber-900">{{ $booking->special_requests }}</p>
                    </div>
                @endif
            </div>

            {{-- Add-ons --}}
            @if($booking->addons->isNotEmpty())
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                    <h3 class="font-bold text-stone-900 mb-4">Add-on Services</h3>
                    <div class="space-y-3">
                        @foreach($booking->addons as $addon)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-stone-900 text-sm">{{ $addon->addonService?->name }}</p>
                                    <p class="text-xs text-stone-400">Qty: {{ $addon->quantity }} × ₱{{ number_format($addon->unit_price, 2) }}</p>
                                </div>
                                <p class="font-semibold text-stone-900">₱{{ number_format($addon->subtotal, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Summary + Actions --}}
        <div class="space-y-5">
            {{-- Room Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Room</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-stone-500">Room Number</span> <span class="font-semibold">{{ $booking->room?->room_number }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-500">Name</span> <span class="font-semibold">{{ $booking->room?->name }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-500">Category</span> <span class="font-semibold">{{ $booking->room?->category?->name }}</span></div>
                </div>
            </div>

            {{-- Pricing Breakdown --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Price Breakdown</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-stone-500">Base Price</span> <span>₱{{ number_format($booking->base_price, 2) }}</span></div>
                    @if($booking->addons_total > 0)
                        <div class="flex justify-between"><span class="text-stone-500">Add-ons</span> <span>₱{{ number_format($booking->addons_total, 2) }}</span></div>
                    @endif
                    @if($booking->pet_fee_total > 0)
                        <div class="flex justify-between"><span class="text-stone-500">Pet Fee</span> <span>₱{{ number_format($booking->pet_fee_total, 2) }}</span></div>
                    @endif
                    @if($booking->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600"><span>Discount</span> <span>-₱{{ number_format($booking->discount_amount, 2) }}</span></div>
                    @endif
                    <div class="border-t border-stone-100 pt-2 mt-2 flex justify-between font-bold text-base">
                        <span>Total</span>
                        <span class="text-emerald-600">₱{{ number_format($booking->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    @if($booking->status === 'pending' || $booking->status === 'confirmed')
                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors">
                                ✅ Mark as Checked In
                            </button>
                        </form>
                    @endif
                    @if($booking->status === 'checked_in')
                        <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition-colors">
                                🏁 Mark as Checked Out
                            </button>
                        </form>
                    @endif
                    @if(!in_array($booking->status, ['cancelled', 'checked_out']))
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}"
                              onsubmit="return confirm('Cancel this booking?')">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-xl text-sm transition-colors border border-red-200">
                                ❌ Cancel Booking
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.bookings.index') }}"
                       class="block w-full py-2.5 text-center text-stone-600 hover:text-stone-900 text-sm font-medium">
                        ← Back to Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
