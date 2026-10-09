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
                            <div><span class="text-stone-500">Guests:</span> <span class="font-semibold">{{ $booking->adults }} {{ Str::plural('Adult', $booking->adults) }}@if($booking->children > 0), {{ $booking->children }} {{ Str::plural('Child', $booking->children) }}@endif @if($booking->pets > 0), 🐾 {{ $booking->pets }} {{ Str::plural('Pet', $booking->pets) }}@endif</span></div>
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

                @if($booking->payment_proof)
                    <div class="mt-6 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wide mb-2">Payment Proof Uploaded</p>
                        <div class="flex items-center gap-4">
                            <a href="{{ asset('storage/'.$booking->payment_proof) }}" target="_blank" class="block w-24 h-24 rounded-lg overflow-hidden border border-emerald-200 bg-white">
                                <img src="{{ asset('storage/'.$booking->payment_proof) }}" alt="Proof" class="w-full h-full object-cover hover:scale-105 transition-transform">
                            </a>
                            <div>
                                <p class="text-sm font-semibold text-stone-900">Method: <span class="capitalize">{{ $booking->payment_method ?? 'Manual' }}</span></p>
                                <a href="{{ asset('storage/'.$booking->payment_proof) }}" target="_blank" class="text-xs text-emerald-700 font-medium hover:underline inline-flex items-center gap-1 mt-1">
                                    View Full Image ↗
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Add-ons & Extra Charges --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-stone-900">Add-ons & Extra Charges</h3>
                    <span class="text-xs font-medium text-stone-400">{{ $booking->addons->count() }} items</span>
                </div>

                @if($booking->addons->isNotEmpty())
                    <div class="space-y-3 mb-6">
                        @foreach($booking->addons as $addon)
                            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-xl">
                                <div>
                                    <p class="font-medium text-stone-900 text-sm">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Custom Service' }}</p>
                                    <p class="text-xs text-stone-400">Qty: {{ $addon->quantity }} × ₱{{ number_format($addon->unit_price, 2) }}</p>
                                </div>
                                <p class="font-semibold text-stone-900">₱{{ number_format($addon->subtotal, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-stone-400 mb-6">No add-ons or extra charges currently attached.</p>
                @endif

                {{-- Form to Attach Additional Bills/Items --}}
                @if(!in_array($booking->status, ['cancelled', 'checked_out']))
                    <div class="pt-4 border-t border-stone-100" x-data="{ isCustom: false }">
                        <p class="text-xs font-bold text-stone-700 uppercase tracking-wide mb-3">+ Attach Extra Item / Service (Snacks, Extra Bed, etc.)</p>
                        <form method="POST" action="{{ route('admin.bookings.add-extra', $booking) }}" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-stone-500 mb-1">Select Service or Custom</label>
                                    <select name="addon_service_id" @change="isCustom = ($event.target.value === '')"
                                            class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="">Custom Item / Snack / Extra Bed</option>
                                        @foreach($availableAddons ?? [] as $addonService)
                                            <option value="{{ $addonService->id }}">{{ $addonService->name }} (₱{{ number_format($addonService->price, 0) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-stone-500 mb-1">Item Description / Name</label>
                                    <input type="text" name="item_name" placeholder="e.g., Extra Bed, Snack Box, Mini-bar"
                                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-stone-500 mb-1">Unit Price (₱)</label>
                                    <input type="number" step="0.01" name="unit_price" placeholder="Leave empty if selecting service"
                                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-stone-500 mb-1">Quantity</label>
                                    <input type="number" name="quantity" value="1" min="1" required
                                           class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-stone-800 hover:bg-stone-900 text-white rounded-xl text-xs font-semibold transition-colors">
                                + Add Charge to Booking
                            </button>
                        </form>
                    </div>
                @endif
            </div>
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
                        <div class="flex justify-between"><span class="text-stone-500">Add-ons & Extras</span> <span>₱{{ number_format($booking->addons_total, 2) }}</span></div>
                    @endif
                    @if($booking->pet_fee_total > 0)
                        <div class="flex justify-between"><span class="text-stone-500">Pet Fee</span> <span>₱{{ number_format($booking->pet_fee_total, 2) }}</span></div>
                    @endif
                    @if($booking->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600"><span>Discount</span> <span>-₱{{ number_format($booking->discount_amount, 2) }}</span></div>
                    @endif
                    <div class="border-t border-stone-100 pt-2 mt-2 flex justify-between font-bold text-base">
                        <span>Total Due</span>
                        <span class="text-emerald-600">₱{{ number_format($booking->total_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-1 text-xs">
                        <span class="text-stone-500">Payment Status</span>
                        <span class="font-bold uppercase {{ $booking->payment_status === 'paid' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $booking->payment_status }}</span>
                    </div>
                </div>
            </div>

            {{-- Settlement & Actions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Settlement & Actions</h3>
                <div class="space-y-3">
                    {{-- Checkout settlement flow: unpaid to paid --}}
                    @if($booking->payment_status !== 'paid' && $booking->status !== 'cancelled')
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 space-y-3">
                            <p class="text-xs font-bold text-emerald-900 uppercase tracking-wide">💳 Settle Payment</p>
                            <form method="POST" action="{{ route('admin.bookings.settle-payment', $booking) }}" class="space-y-2">
                                @csrf
                                <select name="payment_method" class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-medium focus:outline-none">
                                    <option value="cash">Cash Settlement</option>
                                    <option value="card">Credit / Debit Card</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                                    ✓ Settle & Mark as Paid
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($booking->status === 'pending' || $booking->status === 'confirmed')
                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors">
                                ✅ Mark as Checked In
                            </button>
                        </form>
                    @endif

                    @if($booking->status === 'checked_in')
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200">
                            <h4 class="font-bold text-amber-950 text-sm mb-1 flex items-center gap-1.5">
                                <span>🏁</span> Guest Check-Out & Housekeeping
                            </h4>
                            <p class="text-xs text-amber-800 mb-3">
                                Checking out will immediately flag Room {{ $booking->room?->room_number }} as <strong>Housekeeping</strong>. A housekeeper must inspect and clear the room before it is available to occupy again.
                            </p>

                            <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}" class="space-y-3">
                                @csrf

                                @if($booking->payment_status !== 'paid')
                                    <div class="p-3 bg-white/80 rounded-xl border border-amber-300 space-y-2">
                                        <label class="flex items-center gap-2 text-xs font-semibold text-amber-900 cursor-pointer">
                                            <input type="checkbox" name="settle_payment" value="1" checked class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                            <span>Settle balance (₱{{ number_format($booking->total_price, 2) }}) upon check-out</span>
                                        </label>
                                        <div>
                                            <label class="block text-[11px] font-medium text-stone-600 mb-1">Settlement Method</label>
                                            <select name="payment_method" class="w-full px-2.5 py-1.5 bg-white border border-stone-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                <option value="cash">Cash</option>
                                                <option value="card">Credit / Debit Card</option>
                                                <option value="gcash">GCash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-bold text-stone-700 uppercase tracking-wide mb-1">
                                        🧹 Assign Housekeeper
                                    </label>
                                    <select name="housekeeper_id" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="">-- Unassigned (Any Available Housekeeper) --</option>
                                        @if(isset($housekeepers) && $housekeepers->isNotEmpty())
                                            <optgroup label="Housekeeping Staff">
                                                @foreach($housekeepers as $hk)
                                                    <option value="{{ $hk->id }}">{{ $hk->name }} ({{ $hk->email }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(isset($allStaff) && $allStaff->isNotEmpty())
                                            <optgroup label="Other Staff / Admin">
                                                @foreach($allStaff->where('role', '!=', 'housekeeper') as $staff)
                                                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ ucfirst($staff->role) }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-stone-600 mb-1">
                                        Inspection / Cleaning Instructions (Optional)
                                    </label>
                                    <textarea name="housekeeping_notes" rows="2"
                                              placeholder="e.g., Check mini-bar, inspect for damages, replace linens and towels."
                                              class="w-full px-3 py-1.5 bg-white border border-stone-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                                </div>

                                <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                    🏁 Complete Check-Out
                                </button>
                            </form>
                        </div>
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
