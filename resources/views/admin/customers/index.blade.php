<x-layouts.admin>
    <x-slot:heading>Guest Directory & History</x-slot:heading>
    <x-slot:subheading>Member profiles, reservation histories, and lifetime resort value</x-slot:subheading>

    {{-- Search & Stats Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex items-center gap-2 max-w-md w-full">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search guest by name, email, or phone..."
                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-2xs">
                <svg class="w-4 h-4 text-[#777A72] absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] rounded-xl text-xs font-semibold uppercase tracking-wider transition-colors shadow-2xs">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.customers.index') }}" class="text-xs text-[#777A72] hover:text-[#233D32] font-medium">Clear</a>
            @endif
        </form>
    </div>

    {{-- Customer Directory Table --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Customer</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Contact</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Stays / Bookings</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Total Paid</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Last Visit</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#233D32]/5">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#233D32] text-[#F7F5EF] font-serif font-bold text-sm flex items-center justify-center shrink-0 border border-[#A68B61]/30">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-serif font-bold text-sm text-[#233D32]">{{ $customer->name }}</div>
                                        <div class="text-[11px] text-[#777A72] font-light">Member since {{ $customer->created_at->format('M Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <div class="text-[#292D29] font-medium">{{ $customer->email }}</div>
                                <div class="text-[#777A72] text-[11px]">{{ $customer->phone_number ?? 'No phone' }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 bg-[#FAF8F4] border border-[#233D32]/10 text-[#233D32] font-semibold rounded-lg text-xs">
                                    {{ $customer->bookings_count }} stays
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right font-serif font-bold text-sm text-[#233D32]">
                                ₱{{ number_format((float) ($customer->bookings_sum_total_price ?? 0), 2) }}
                            </td>
                            <td class="px-4 py-4 text-xs text-[#595D58]">
                                {{ $customer->last_visited_at ? $customer->last_visited_at->format('M d, Y') : ($customer->visit_count > 0 ? "{$customer->visit_count} visits" : '—') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}"
                                   class="px-3 py-1.5 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-xs font-semibold rounded-xl transition-colors">
                                    History →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-[#777A72] text-xs font-light">
                                No customer records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-[#233D32]/10 bg-[#FAF8F4]">{{ $customers->links() }}</div>
        @endif
    </div>
</x-layouts.admin>