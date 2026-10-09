<x-layouts.admin>
    <x-slot:heading>New Discount Code</x-slot:heading>
    <x-slot:subheading>Create a promo code for bookings</x-slot:subheading>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.discounts.store') }}" class="space-y-5">
            @csrf

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Promo Code * <span class="text-xs text-stone-400">(uppercase)</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. SUMMER20"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase @error('code') border-red-400 @enderror"
                               oninput="this.value = this.value.toUpperCase()">
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Type *</label>
                        <select name="type" x-model="type" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Value *</label>
                        <input type="number" name="value" value="{{ old('value') }}" min="0" step="0.01" required
                               placeholder="e.g. 20 for 20% or 500 for ₱500"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Minimum Booking Amount (₱)</label>
                        <input type="number" name="minimum_amount" value="{{ old('minimum_amount') }}" min="0" step="0.01"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Max Uses</label>
                        <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" placeholder="Leave blank for unlimited"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Valid From</label>
                        <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Internal note about this code"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <label class="flex items-center gap-2 cursor-pointer mt-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-stone-700">Active (usable immediately)</span>
                </label>
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl">
                    @foreach($errors->all() as $error)<p class="text-sm text-red-700">• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-emerald-200">
                    Create Discount
                </button>
                <a href="{{ route('admin.discounts.index') }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-sm hover:bg-stone-50">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
