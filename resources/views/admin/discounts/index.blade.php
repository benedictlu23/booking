<x-layouts.admin>
    <x-slot:heading>Promotions & Privilege Rates</x-slot:heading>
    <x-slot:subheading>Manage resort booking discounts, promotional voucher codes, and special rates</x-slot:subheading>

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.discounts.create') }}"
           class="px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-widest rounded-xl transition-all shadow-xs">
            + Create New Promotion
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                    <tr>
                        @foreach(['Promo Code', 'Discount Type', 'Benefit Value', 'Minimum Spend', 'Redemptions', 'Valid Until', 'Status', 'Actions'] as $h)
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#233D32]/5">
                    @forelse($discounts as $discount)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-4 py-4 font-mono font-bold text-[#A68B61] text-xs tracking-wider">{{ $discount->code }}</td>
                            <td class="px-4 py-4 capitalize text-[#595D58] font-medium">{{ $discount->type }}</td>
                            <td class="px-4 py-4 font-serif font-bold text-sm text-[#233D32]">
                                {{ $discount->type === 'percentage' ? $discount->value.'%' : '₱'.number_format($discount->value, 2) }}
                            </td>
                            <td class="px-4 py-4 text-[#777A72]">{{ $discount->minimum_amount ? '₱'.number_format($discount->minimum_amount, 2) : '—' }}</td>
                            <td class="px-4 py-4 text-[#595D58] font-mono">{{ $discount->used_count }} / {{ $discount->max_uses ?? '∞' }}</td>
                            <td class="px-4 py-4 text-[#777A72]">{{ $discount->valid_until?->format('M d, Y') ?? 'No expiry' }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider border {{ $discount->is_active && $discount->isValidNow() ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-stone-100 text-stone-600 border-stone-200' }}">
                                    {{ $discount->is_active && $discount->isValidNow() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.discounts.edit', $discount) }}"
                                       class="px-2.5 py-1 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-[11px] font-semibold rounded-lg transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}"
                                          onsubmit="return confirm('Delete this discount code?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 border border-rose-200 text-rose-700 hover:bg-rose-50 text-[11px] font-semibold rounded-lg transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center text-[#777A72]">
                                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3 text-[#A68B61]">
                                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                                <h3 class="font-serif font-bold text-base text-[#233D32]">No Promotional Rates Found</h3>
                                <a href="{{ route('admin.discounts.create') }}" class="mt-2 inline-block text-xs uppercase tracking-wider font-semibold text-[#A68B61] hover:underline">Create first promotion →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>