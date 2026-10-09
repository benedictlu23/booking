<x-layouts.admin>
    <x-slot:heading>Discounts & Promo Codes</x-slot:heading>
    <x-slot:subheading>Manage booking discounts and promotions</x-slot:subheading>

    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.discounts.create') }}"
           class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
            + New Discount
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-200">
                <tr>
                    @foreach(['Code', 'Type', 'Value', 'Min Amount', 'Uses', 'Valid Until', 'Status', 'Actions'] as $h)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @forelse($discounts as $discount)
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-4 py-4 font-mono font-bold text-emerald-600">{{ $discount->code }}</td>
                        <td class="px-4 py-4 capitalize text-stone-600">{{ $discount->type }}</td>
                        <td class="px-4 py-4 font-semibold text-stone-900">
                            {{ $discount->type === 'percentage' ? $discount->value.'%' : '₱'.number_format($discount->value, 2) }}
                        </td>
                        <td class="px-4 py-4 text-stone-500">{{ $discount->minimum_amount ? '₱'.number_format($discount->minimum_amount, 2) : '—' }}</td>
                        <td class="px-4 py-4 text-stone-600">{{ $discount->used_count }} / {{ $discount->max_uses ?? '∞' }}</td>
                        <td class="px-4 py-4 text-stone-500">{{ $discount->valid_until?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $discount->is_active && $discount->isValidNow() ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}">
                                {{ $discount->is_active && $discount->isValidNow() ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.discounts.edit', $discount) }}"
                                   class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-medium rounded-lg transition-colors">Edit</a>
                                <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}"
                                      onsubmit="return confirm('Delete this discount?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-stone-400">
                            <div class="text-4xl mb-3">🏷️</div>
                            <p class="font-medium">No discounts yet</p>
                            <a href="{{ route('admin.discounts.create') }}" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">Create your first discount →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
