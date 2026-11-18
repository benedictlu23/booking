<x-layouts.admin>
    <x-slot:heading>Reservation Folio</x-slot:heading>
    <x-slot:subheading>Record details for {{ $booking->booking_code }}</x-slot:subheading>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Main Folio Information --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6 border-b border-[#233D32]/10 pb-6">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Master Folio</span>
                        <p class="font-mono text-2xl sm:text-3xl font-bold text-[#233D32] mt-0.5">{{ $booking->booking_code }}</p>
                        <p class="text-xs text-[#777A72] mt-1 font-light">Created {{ $booking->created_at->diffForHumans() }} ({{ $booking->created_at->format('M d, Y · h:i A') }})</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-50 text-amber-900 border-amber-200',
                                'confirmed' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                'checked_out' => 'bg-stone-100 text-stone-800 border-stone-200',
                                'cancelled' => 'bg-rose-50 text-rose-900 border-rose-200',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-xl text-xs uppercase tracking-wider font-semibold border {{ $statusColors[$booking->status] ?? 'bg-stone-100 text-stone-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                        <span class="px-3 py-1 rounded-xl text-xs uppercase tracking-wider font-semibold border {{ $booking->payment_status === 'paid' ? 'bg-[#A68B61]/15 text-[#172A22] border-[#A68B61]/30' : ($booking->payment_status === 'partial' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-rose-50 text-rose-800 border-rose-200') }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                        @if($booking->is_walk_in)
                            <span class="px-3 py-1 rounded-xl text-xs uppercase tracking-wider font-semibold bg-[#FAF8F4] text-[#233D32] border border-[#233D32]/20">Walk-In</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] mb-3">Guest Information</p>
                        <div class="space-y-2 text-xs">
                            <div><span class="text-[#777A72]">Name:</span> <span class="font-serif font-bold text-sm text-[#233D32] ml-1">{{ $booking->guest_name }}</span></div>
                            @if($booking->guest_email)
                                <div><span class="text-[#777A72]">Email:</span> <span class="font-medium text-[#292D29] ml-1">{{ $booking->guest_email }}</span></div>
                            @endif
                            @if($booking->guest_phone)
                                <div><span class="text-[#777A72]">Phone:</span> <span class="font-medium text-[#292D29] ml-1">{{ $booking->guest_phone }}</span></div>
                            @endif
                            @if($booking->user)
                                <div class="flex items-center gap-2 mt-3 pt-2 border-t border-[#233D32]/10">
                                    @if($booking->user->avatar)
                                        <img src="{{ $booking->user->avatar }}" class="w-6 h-6 rounded-lg object-cover">
                                    @endif
                                    <span class="text-[11px] text-[#A68B61] font-semibold">Registered Member · {{ $booking->user->visit_count }} visits</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] mb-3">Stay Details</p>
                        <div class="space-y-2 text-xs">
                            <div><span class="text-[#777A72]">Check-In:</span> <span class="font-semibold text-[#292D29] ml-1">{{ $booking->check_in->format('D, M d, Y') }}</span></div>
                            <div><span class="text-[#777A72]">Check-Out:</span> <span class="font-semibold text-[#292D29] ml-1">{{ $booking->check_out->format('D, M d, Y') }}</span></div>
                            <div><span class="text-[#777A72]">Duration:</span> <span class="font-semibold text-[#292D29] ml-1">{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }} ({{ $booking->booking_type === 'day_pass' ? 'Day Pass' : 'Overnight' }})</span></div>
                            <div><span class="text-[#777A72]">Party Size:</span> <span class="font-semibold text-[#292D29] ml-1">{{ $booking->adults }} {{ Str::plural('Adult', $booking->adults) }}@if($booking->children > 0), {{ $booking->children }} {{ Str::plural('Child', $booking->children) }}@endif @if($booking->pets > 0), 🐾 {{ $booking->pets }} {{ Str::plural('Pet', $booking->pets) }}@endif</span></div>
                        </div>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div class="mt-6 p-4 bg-[#FAF8F4] rounded-xl border border-[#233D32]/10">
                        <p class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] mb-1">Special Requests</p>
                        <p class="text-xs text-[#292D29] font-light leading-relaxed">{{ $booking->special_requests }}</p>
                    </div>
                @endif

                {{-- Payment Proof Verification Box --}}
                @if($booking->payment_proof)
                    <div class="mt-6 p-5 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/15">
                        <div class="flex items-start justify-between mb-3.5">
                            <div>
                                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Verification</span>
                                <p class="text-xs font-bold text-[#233D32]">Payment Slip Uploaded</p>
                                <p class="text-[11px] text-[#777A72]">Method: <strong class="capitalize text-[#292D29]">{{ $booking->payment_method ?? 'Online Transfer' }}</strong></p>
                            </div>
                            @if($booking->balance_due > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                    Needs Verification
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#233D32]/10 text-[#233D32] border border-[#233D32]/20">
                                    Verified & Paid
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ asset('storage/'.$booking->payment_proof) }}" target="_blank" class="block w-24 h-24 rounded-xl overflow-hidden border border-[#233D32]/20 shadow-xs bg-white shrink-0 group">
                                <img src="{{ asset('storage/'.$booking->payment_proof) }}" alt="Payment Proof" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </a>
                            <div class="space-y-2">
                                <a href="{{ asset('storage/'.$booking->payment_proof) }}" target="_blank" class="text-xs text-[#A68B61] font-bold hover:underline inline-flex items-center gap-1">
                                    <span>🔍 Inspect full receipt screenshot ↗</span>
                                </a>
                                @if($booking->balance_due > 0 && $booking->status !== 'cancelled')
                                    <p class="text-[11px] text-[#777A72]">
                                        Remaining balance: <strong class="text-[#233D32]">₱{{ number_format($booking->balance_due, 2) }}</strong>
                                    </p>
                                    <form method="POST" action="{{ route('admin.bookings.settle-payment', $booking) }}" class="pt-0.5">
                                        @csrf
                                        <input type="hidden" name="payment_method" value="{{ in_array($booking->payment_method, ['gcash', 'bank_transfer', 'card', 'cash']) ? $booking->payment_method : 'gcash' }}">
                                        <input type="hidden" name="amount" value="{{ $booking->balance_due }}">
                                        <button type="submit" class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] rounded-xl text-xs font-semibold uppercase tracking-wider transition-all shadow-2xs">
                                            ✓ Verify &amp; Settle Full Balance
                                        </button>
                                    </form>
                                @else
                                    <p class="text-xs text-emerald-800 font-semibold flex items-center gap-1">
                                        <span>✅</span> Payment verified and fully recorded.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Add-ons & Extra Charges --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Incidental Charges</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Add-ons & Extras</h3>
                    </div>
                    <span class="text-xs text-[#777A72]">{{ $booking->addons->count() }} line items</span>
                </div>

                @if($booking->addons->isNotEmpty())
                    <div class="space-y-2.5 mb-6">
                        @foreach($booking->addons as $addon)
                            <div class="flex items-center justify-between p-3.5 bg-[#FAF8F4] border border-[#233D32]/10 rounded-xl">
                                <div>
                                    <p class="font-medium text-[#292D29] text-xs">{{ $addon->item_name ?? $addon->addonService?->name ?? 'Custom Service' }}</p>
                                    <p class="text-[11px] text-[#777A72]">Qty: {{ $addon->quantity }} × ₱{{ number_format($addon->unit_price, 2) }}</p>
                                </div>
                                <p class="font-serif font-bold text-sm text-[#233D32]">₱{{ number_format($addon->subtotal, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-[#777A72] mb-6 font-light">No additional amenities or extra services attached to this folio.</p>
                @endif

                {{-- Form to Attach Additional Bills/Items --}}
                @if(!in_array($booking->status, ['cancelled', 'checked_out']))
                    <div class="pt-4 border-t border-[#233D32]/10" x-data="{ isCustom: false }">
                        <span class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em] block mb-3">+ Attach Extra Item / Service (Mini-Bar, Extra Bed, Food)</span>
                        <form method="POST" action="{{ route('admin.bookings.add-extra', $booking) }}" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Select Service or Custom</label>
                                    <select name="addon_service_id" @change="isCustom = ($event.target.value === '')"
                                            class="w-full px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                        <option value="">Custom Item / Snack / Extra Bed</option>
                                        @foreach($availableAddons ?? [] as $addonService)
                                            <option value="{{ $addonService->id }}">{{ $addonService->name }} (₱{{ number_format($addonService->price, 0) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Item Description / Name</label>
                                    <input type="text" name="item_name" placeholder="e.g., Extra Bed, Snack Box, Mini-bar"
                                           class="w-full px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Unit Price (₱)</label>
                                    <input type="number" step="0.01" name="unit_price" placeholder="Leave empty if service chosen"
                                           class="w-full px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Quantity</label>
                                    <input type="number" name="quantity" value="1" min="1" required
                                           class="w-full px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                </div>
                            </div>
                            <button type="submit" class="px-4 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] rounded-xl text-xs font-semibold uppercase tracking-wider transition-all shadow-2xs">
                                + Add Charge to Folio
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="space-y-6">
            {{-- Room Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Assigned Property</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg mb-3">Room Information</h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between"><span class="text-[#777A72]">Unit Number</span> <span class="font-serif font-bold text-[#233D32]">#{{ $booking->room?->room_number }}</span></div>
                    <div class="flex justify-between"><span class="text-[#777A72]">Room Name</span> <span class="font-semibold text-[#292D29]">{{ $booking->room?->name }}</span></div>
                    <div class="flex justify-between"><span class="text-[#777A72]">Category</span> <span class="font-medium text-[#78866B]">{{ $booking->room?->category?->name }}</span></div>
                </div>
            </div>

            {{-- Pricing Breakdown --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Financial Folio</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg mb-4 border-b border-[#233D32]/10 pb-2">Billing Breakdown</h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-[#777A72]"><span>Base Price ({{ $booking->nights }} nights)</span> <span class="font-medium text-[#292D29]">₱{{ number_format($booking->base_price, 2) }}</span></div>
                    @if($booking->addons_total > 0)
                        <div class="flex justify-between text-[#777A72]"><span>Add-ons & Extras</span> <span class="font-medium text-[#292D29]">₱{{ number_format($booking->addons_total, 2) }}</span></div>
                    @endif
                    @if($booking->pet_fee_total > 0)
                        <div class="flex justify-between text-[#A68B61]"><span>Pet Accommodation</span> <span class="font-medium">₱{{ number_format($booking->pet_fee_total, 2) }}</span></div>
                    @endif
                    @if($booking->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700"><span>Privilege Discount</span> <span class="font-medium">-₱{{ number_format($booking->discount_amount, 2) }}</span></div>
                    @endif
                    <div class="border-t border-[#233D32]/10 pt-3 mt-1 flex justify-between font-serif font-bold text-sm text-[#233D32]">
                        <span>Total Charges</span>
                        <span class="text-lg">₱{{ number_format($booking->total_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs pt-1 text-[#777A72]">
                        <span>Amount Settled</span>
                        <span class="font-semibold text-emerald-800">₱{{ number_format($booking->amount_paid ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs pt-1 border-t border-dashed border-[#233D32]/10">
                        <span class="font-semibold text-[#292D29]">Balance Due</span>
                        <span class="font-bold text-sm {{ $booking->balance_due > 0 ? 'text-[#A68B61]' : 'text-emerald-800' }}">
                            ₱{{ number_format($booking->balance_due, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between pt-2 text-xs border-t border-[#233D32]/10">
                        <span class="text-[#777A72]">Payment Status</span>
                        <span class="font-semibold uppercase text-[10px] tracking-wider px-2 py-0.5 rounded-md border {{ $booking->payment_status === 'paid' ? 'bg-[#A68B61]/15 text-[#172A22] border-[#A68B61]/30' : ($booking->payment_status === 'partial' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-rose-50 text-rose-800 border-rose-200') }}">
                            {{ $booking->payment_status }}
                        </span>
                    </div>
                    @if($booking->payment_method)
                        <div class="flex justify-between text-xs pt-0.5">
                            <span class="text-[#777A72]">Payment Method</span>
                            <span class="font-medium text-[#292D29] capitalize">{{ $booking->payment_method }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Settlement & Actions --}}
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Front Desk Actions</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg mb-2">Settlement & Flow</h3>

                <div class="space-y-3">
                    {{-- Settle form when balance is due --}}
                    @if($booking->balance_due > 0 && $booking->status !== 'cancelled')
                        <div class="p-4 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/15 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-[#233D32]">Record Payment</span>
                                <span class="text-xs font-bold text-[#A68B61]">Due: ₱{{ number_format($booking->balance_due, 2) }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.bookings.settle-payment', $booking) }}" class="space-y-2.5">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Amount (₱)</label>
                                        <input type="number" step="0.01" name="amount" value="{{ $booking->balance_due }}" max="{{ $booking->balance_due }}" min="0.01" required
                                               class="w-full px-2.5 py-1.5 bg-white border border-[#233D32]/15 rounded-lg text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Method</label>
                                        <select name="payment_method" class="w-full px-2.5 py-1.5 bg-white border border-[#233D32]/15 rounded-lg text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="gcash">GCash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-wider transition-all shadow-2xs">
                                    Record Payment
                                </button>
                            </form>
                        </div>
                    @elseif($booking->status !== 'cancelled')
                        <div class="p-3 bg-[#FAF8F4] rounded-xl border border-[#233D32]/10 text-xs font-medium text-[#233D32] flex items-center gap-2">
                            <span>✅</span> Folio charges fully settled.
                        </div>
                    @endif

                    @if($booking->status === 'pending' || $booking->status === 'confirmed')
                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-sm">
                                Check In Guest
                            </button>
                        </form>
                    @endif

                    @if($booking->status === 'checked_in')
                        <div class="p-4 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/15">
                            <h4 class="font-serif font-bold text-[#233D32] text-sm mb-1">
                                Complete Check-Out & Housekeeping
                            </h4>
                            <p class="text-[11px] text-[#777A72] mb-3 font-light leading-relaxed">
                                Departure will flag Room {{ $booking->room?->room_number }} for Housekeeping inspection before next occupancy.
                            </p>

                            <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}" class="space-y-3">
                                @csrf

                                @if($booking->balance_due > 0)
                                    <div class="p-3 bg-white rounded-xl border border-[#233D32]/15 space-y-2">
                                        <label class="flex items-center gap-2 text-xs font-semibold text-[#233D32] cursor-pointer">
                                            <input type="checkbox" name="settle_payment" value="1" checked class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                                            <span>Collect balance (₱{{ number_format($booking->balance_due, 2) }}) upon departure</span>
                                        </label>
                                        <div>
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">Settlement Method</label>
                                            <select name="payment_method" class="w-full px-2.5 py-1.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-lg text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                                <option value="cash">Cash</option>
                                                <option value="card">Credit / Debit Card</option>
                                                <option value="gcash">GCash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">
                                        Assign Housekeeper
                                    </label>
                                    <select name="housekeeper_id" class="w-full px-3 py-2 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                        <option value="">-- Unassigned (Any Available Staff) --</option>
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
                                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#777A72] mb-1">
                                        Cleaning Instructions (Optional)
                                    </label>
                                    <textarea name="housekeeping_notes" rows="2"
                                              placeholder="e.g., Check mini-bar, inspect for damages, replace linens."
                                              class="w-full px-3 py-2 bg-white border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none"></textarea>
                                </div>

                                <button type="submit" class="w-full py-3 bg-[#A68B61] hover:bg-[#8C734D] text-[#172A22] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-sm">
                                    Complete Departure
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(!in_array($booking->status, ['cancelled', 'checked_out']))
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}"
                              onsubmit="return confirm('Cancel this booking?')">
                            @csrf
                            <button type="submit" class="w-full py-2.5 border border-rose-300 text-rose-700 hover:bg-rose-50 font-semibold rounded-xl text-xs uppercase tracking-wider transition-all">
                                Cancel Booking
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.bookings.index') }}"
                       class="block w-full py-2.5 text-center text-[#777A72] hover:text-[#233D32] text-xs uppercase tracking-wider font-semibold transition-colors">
                        ← Back to Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>