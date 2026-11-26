<x-layouts.admin>
    <x-slot:heading>Create Payment Method</x-slot:heading>
    <x-slot:subheading>Add a new payment gateway, digital wallet, or bank transfer option for guest reservations</x-slot:subheading>

    <div class="max-w-2xl bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80">
        <form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Display Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. GCash, BDO Unibank, Maya"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Unique Code Identifier *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. gcash, bdo, maya, cash"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm font-mono text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    <p class="text-[11px] text-stone-400 mt-1">Alphanumeric identifier, lowercase dash/underscore</p>
                    @error('code') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Account Name / Beneficiary</label>
                    <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="e.g. Agro-Inland Resort Inc."
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('account_name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Account Number / Mobile</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="e.g. 0917-123-4567 or 1234-5678-90"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm font-mono text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('account_number') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Payment Instructions / Notes</label>
                <textarea name="instructions" rows="3" placeholder="Step-by-step instructions displayed to guests during checkout (e.g. upload payment screenshot with reference number)."
                          class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none">{{ old('instructions') }}</textarea>
                @error('instructions') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">QR Code Image (Optional)</label>
                <input type="file" name="qr_code" accept="image/*"
                       class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FAF8F4] file:text-[#233D32] hover:file:bg-[#EFEBE1] cursor-pointer">
                <p class="text-[11px] text-stone-400 mt-1">Scannable QR image for direct guest scanning and downloading (PNG, JPG, SVG max 2MB)</p>
                @error('qr_code') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Sort Display Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('sort_order') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="is_enabled" id="is_enabled" value="1" {{ old('is_enabled', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-[#233D32] border-stone-300 rounded focus:ring-[#233D32]">
                    <label for="is_enabled" class="text-xs font-semibold uppercase tracking-wider text-[#292D29] cursor-pointer">
                        Enable this payment method
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-5 border-t border-stone-100">
                <button type="submit" class="px-7 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                    Create Payment Method
                </button>
                <a href="{{ route('admin.payment-methods.index') }}" class="px-5 py-3 text-stone-600 hover:text-[#233D32] text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
