<x-layouts.admin>
    <x-slot:heading>Edit Discount</x-slot:heading>
    <x-slot:subheading>{{ $discount->code }}</x-slot:subheading>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.discounts.update', $discount) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Code *</label>
                        <input type="text" name="code" value="{{ old('code', $discount->code) }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase @error('code') border-red-400 @enderror"
                               oninput="this.value = this.value.toUpperCase()">
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Type *</label>
                        <select name="type" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="percentage" {{ old('type', $discount->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('type', $discount->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (₱)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Value *</label>
                        <input type="number" name="value" value="{{ old('value', $discount->value) }}" min="0" step="0.01" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Minimum Amount (₱)</label>
                        <input type="number" name="minimum_amount" value="{{ old('minimum_amount', $discount->minimum_amount) }}" min="0" step="0.01"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Valid From</label>
                        <input type="date" name="valid_from" value="{{ old('valid_from', $discount->valid_from?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', $discount->valid_until?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Max Uses</label>
                    <input type="number" name="max_uses" value="{{ old('max_uses', $discount->max_uses) }}" min="1"
                           placeholder="Unlimited"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
                    <input type="text" name="description" value="{{ old('description', $discount->description) }}"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $discount->is_active) ? 'checked' : '' }}
                           class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-stone-700">Active</span>
                </label>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
                Used <strong>{{ $discount->used_count }}</strong> / {{ $discount->max_uses ?? '∞' }} times.
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors">Update Discount</button>
                <a href="{{ route('admin.discounts.index') }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-sm hover:bg-stone-50">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
