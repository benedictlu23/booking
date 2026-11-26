<x-layouts.admin>
    <x-slot:heading>Payment Methods</x-slot:heading>
    <x-slot:subheading>Manage accepted resort reservation payment gateways, digital wallets, and banking credentials</x-slot:subheading>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                ← General Settings
            </a>
            <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                Room Categories
            </a>
            <a href="{{ route('admin.amenities.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                Amenities
            </a>
        </div>
        <a href="{{ route('admin.payment-methods.create') }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-medium uppercase tracking-wider rounded-xl transition-colors shadow-xs">
            + Add Payment Method
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xs border border-stone-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#FAF8F4] border-b border-stone-200/80 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-6 py-3.5">Method</th>
                        <th class="px-6 py-3.5">Code Identifier</th>
                        <th class="px-6 py-3.5">Account Details</th>
                        <th class="px-6 py-3.5">QR Code</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($paymentMethods as $method)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-6 py-4 font-serif font-bold text-[#172A22] text-base">
                                {{ $method->name }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-[#A68B61]">
                                {{ $method->code }}
                            </td>
                            <td class="px-6 py-4 text-xs text-stone-600 space-y-0.5">
                                @if($method->account_name)
                                    <div><span class="text-stone-400">Name:</span> <span class="font-medium text-[#292D29]">{{ $method->account_name }}</span></div>
                                @endif
                                @if($method->account_number)
                                    <div><span class="text-stone-400">No:</span> <span class="font-mono font-medium text-[#292D29]">{{ $method->account_number }}</span></div>
                                @endif
                                @if(! $method->account_name && ! $method->account_number)
                                    <span class="text-stone-400 italic">No account numbers specified</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($method->qr_code_path)
                                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-stone-200 bg-white p-1">
                                        <img src="{{ $method->qr_code_url ?? (str_starts_with($method->qr_code_path, 'http') ? $method->qr_code_path : asset('storage/' . $method->qr_code_path)) }}" alt="{{ $method->name }} QR" class="w-full h-full object-contain">
                                    </div>
                                @else
                                    <span class="text-stone-400 text-xs italic">No QR code</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.payment-methods.toggle', $method) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition-colors
                                            {{ $method->is_enabled ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                                        {{ $method->is_enabled ? 'Active / Enabled' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.payment-methods.edit', $method) }}"
                                       class="px-3 py-1.5 bg-[#EFEBE1] hover:bg-[#233D32] text-[#233D32] hover:text-[#F7F5EF] text-xs font-medium rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}"
                                          onsubmit="return confirm('Permanently remove {{ $method->name }} payment method?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-stone-400 font-serif text-sm">
                                No payment methods configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paymentMethods->hasPages())
            <div class="px-6 py-4 border-t border-stone-200/80 bg-[#FAF8F4]">{{ $paymentMethods->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
