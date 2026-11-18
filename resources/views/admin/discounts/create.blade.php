<x-layouts.admin>
    <x-slot:heading>Create Promotional Privilege</x-slot:heading>
    <x-slot:subheading>Define promotional rates, special seasonal voucher codes, and booking privileges</x-slot:subheading>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.discounts.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Parameters</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Privilege Code Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Promo Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. SUMMER20"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-mono font-bold text-[#233D32] uppercase focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] @error('code') border-rose-400 bg-rose-50 @enderror"
                               oninput="this.value = this.value.toUpperCase()">
                        @error('code') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Type *</label>
                        <select name="type" class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Discount Value *</label>
                        <input type="number" name="value" value="{{ old('value') }}" min="0" step="0.01" required
                               placeholder="e.g. 20 for 20% or 500 for ₱500"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#233D32] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Minimum Booking Amount (₱)</label>
                        <input type="number" name="minimum_amount" value="{{ old('minimum_amount') }}" min="0" step="0.01" placeholder="e.g. 3000"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Max Uses</label>
                        <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" placeholder="Unlimited"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Valid From</label>
                        <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Internal Description / Notes</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Campaign name, notes, or terms"
                           class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer pt-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                    <span class="text-xs font-semibold text-[#233D32]">Active (Immediately redeemable on reservations)</span>
                </label>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                    @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-8 py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_15px_rgba(35,61,50,0.18)]">
                    Create Promotion
                </button>
                <a href="{{ route('admin.discounts.index') }}" class="px-6 py-3.5 border border-[#233D32]/15 hover:bg-white text-[#777A72] hover:text-[#233D32] rounded-xl text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>