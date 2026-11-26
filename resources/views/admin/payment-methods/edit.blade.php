<x-layouts.admin>
    <x-slot:heading>Edit Payment Method</x-slot:heading>
    <x-slot:subheading>Update gateway parameters, account numbers, and QR code for {{ $paymentMethod->name }}</x-slot:subheading>

    <div class="max-w-2xl bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80">
        <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Display Name *</label>
                    <input type="text" name="name" value="{{ old('name', $paymentMethod->name) }}" required
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Unique Code Identifier *</label>
                    <input type="text" name="code" value="{{ old('code', $paymentMethod->code) }}" required
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm font-mono text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('code') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Account Name / Beneficiary</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $paymentMethod->account_name) }}"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('account_name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Account Number / Mobile</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $paymentMethod->account_number) }}"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm font-mono text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('account_number') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Payment Instructions / Notes</label>
                <textarea name="instructions" rows="3"
                          class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                @error('instructions') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">QR Code Image</label>
                @if($paymentMethod->qr_code_path)
                    <div class="flex items-center gap-4 mb-3 p-3 bg-[#FAF8F4] border border-stone-200/80 rounded-xl">
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-stone-300 bg-white p-1">
                            <img src="{{ $paymentMethod->qr_code_url ?? (str_starts_with($paymentMethod->qr_code_path, 'http') ? $paymentMethod->qr_code_path : asset('storage/' . $paymentMethod->qr_code_path)) }}" alt="{{ $paymentMethod->name }} QR" class="w-full h-full object-contain">
                        </div>
                        <div class="flex-1 text-xs">
                            <p class="font-medium text-[#292D29]">Current QR Code Uploaded</p>
                            <label class="inline-flex items-center gap-2 mt-1 text-rose-600 cursor-pointer">
                                <input type="checkbox" name="remove_qr_code" value="1" class="w-3.5 h-3.5 rounded text-rose-600 border-stone-300">
                                <span>Remove current QR image</span>
                            </label>
                        </div>
                    </div>
                @endif
                <input type="file" name="qr_code" accept="image/*"
                       class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FAF8F4] file:text-[#233D32] hover:file:bg-[#EFEBE1] cursor-pointer">
                <p class="text-[11px] text-stone-400 mt-1">Upload a new image to replace current QR code (PNG, JPG, SVG max 2MB)</p>
                @error('qr_code') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Sort Display Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $paymentMethod->sort_order) }}" min="0"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    @error('sort_order') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="is_enabled" id="is_enabled" value="1" {{ old('is_enabled', $paymentMethod->is_enabled) ? 'checked' : '' }}
                           class="w-4 h-4 text-[#233D32] border-stone-300 rounded focus:ring-[#233D32]">
                    <label for="is_enabled" class="text-xs font-semibold uppercase tracking-wider text-[#292D29] cursor-pointer">
                        Enable this payment method
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-5 border-t border-stone-100">
                <button type="submit" class="px-7 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                    Save Changes
                </button>
                <a href="{{ route('admin.payment-methods.index') }}" class="px-5 py-3 text-stone-600 hover:text-[#233D32] text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
