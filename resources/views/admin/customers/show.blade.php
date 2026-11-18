<x-layouts.admin>
    <x-slot:heading>{{ $user->name }} — Customer History</x-slot:heading>
    <x-slot:subheading>Member profile overview and detailed reservation records</x-slot:subheading>

    <div class="mb-6">
        <a href="{{ route('admin.customers.index') }}" class="text-xs uppercase tracking-wider font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors flex items-center gap-1.5">
            <span>←</span>
            <span>Back to Guest Directory</span>
        </a>
    </div>

    {{-- Customer Profile & Lifetime Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 md:col-span-1">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-[#233D32] text-[#F7F5EF] font-serif font-bold text-lg flex items-center justify-center border border-[#A68B61]/30">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-serif font-bold text-base text-[#233D32]">{{ $user->name }}</h3>
                    <p class="text-[10px] uppercase tracking-wider text-[#A68B61] font-semibold">Registered Member</p>
                </div>
            </div>
            <div class="space-y-2 text-xs text-[#595D58] border-t border-[#233D32]/10 pt-4">
                <div><span class="font-semibold text-[#292D29]">Email:</span> {{ $user->email }}</div>
                <div><span class="font-semibold text-[#292D29]">Phone:</span> {{ $user->phone_number ?? 'Not provided' }}</div>
                <div><span class="font-semibold text-[#292D29]">Joined:</span> {{ $user->created_at->format('M d, Y') }}</div>
                <div><span class="font-semibold text-[#292D29]">Total Visits:</span> {{ $user->visit_count }} times</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 flex flex-col justify-between">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A68B61]">Lifetime Revenue</p>
            <p class="text-2xl font-serif font-bold text-[#233D32] mt-2">₱{{ number_format($totalSpent, 2) }}</p>
            <p class="text-[11px] text-[#777A72] mt-1 font-light">Gross settled bookings</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 flex flex-col justify-between">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A68B61]">Completed Stays</p>
            <p class="text-2xl font-serif font-bold text-[#233D32] mt-2">{{ $completedStays }}</p>
            <p class="text-[11px] text-[#777A72] mt-1 font-light">Completed check-outs</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 flex flex-col justify-between">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A68B61]">Active / Upcoming</p>
            <p class="text-2xl font-serif font-bold text-[#233D32] mt-2">{{ $activeBookings }}</p>
            <p class="text-[11px] text-[#777A72] mt-1 font-light">In-house or confirmed</p>
        </div>
    </div>

    {{-- Stay History Table --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#233D32]/10">
            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Guest Activity</span>
            <h3 class="font-serif font-bold text-[#233D32] text-base">Stay &amp; Reservation History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Folio Code</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Room</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Dates</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Status</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Payment</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Total</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#233D32]/5">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-4 py-4 font-mono font-bold text-[#A68B61] text-xs">
                                {{ $booking->booking_code }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-serif font-bold text-sm text-[#233D32]">Room {{ $booking->room?->room_number }}</div>
                                <div class="text-[11px] text-[#777A72]">{{ $booking->room?->category?->name ?? 'Room' }}</div>
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <div class="font-medium text-[#292D29]">{{ $booking->check_in->format('M d, Y') }} → {{ $booking->check_out->format('M d, Y') }}</div>
                                <div class="text-[#777A72] text-[11px] font-light">{{ $booking->check_in->diffInDays($booking->check_out) }} nights · {{ ucfirst($booking->booking_type) }}</div>
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-amber-50 text-amber-900 border-amber-200',
                                        'confirmed' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                        'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                        'checked_out' => 'bg-stone-100 text-stone-700 border-stone-200',
                                        'cancelled' => 'bg-rose-50 text-rose-900 border-rose-200',
                                    ];
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $statusBadges[$booking->status] ?? 'bg-stone-100 text-stone-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold border {{ $booking->payment_status === 'paid' ? 'bg-[#A68B61]/15 text-[#172A22] border-[#A68B61]/30' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                                @if($booking->payment_method)
                                    <span class="text-[#777A72] capitalize block mt-0.5 text-[10px]">via {{ $booking->payment_method }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-serif font-bold text-sm text-[#233D32]">
                                ₱{{ number_format($booking->total_price, 2) }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="px-3 py-1.5 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-xs font-semibold rounded-xl transition-colors">
                                    View Folio
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-[#777A72] text-xs font-light">
                                No previous stays or bookings found for this customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-[#233D32]/10 bg-[#FAF8F4]">{{ $bookings->links() }}</div>
        @endif
    </div>
</x-layouts.admin>