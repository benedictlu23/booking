<x-layouts.admin>
    <x-slot:heading>Edit Category: {{ $category->name }}</x-slot:heading>
    <x-slot:subheading>Update accommodation tier details and settings</x-slot:subheading>

    <div class="max-w-xl bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                @error('name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Editorial Narrative / Highlights</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none">{{ old('description', $category->description) }}</textarea>
                @error('description') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Icon Representation</label>
                    <input type="text" name="icon" value="{{ old('icon', $category->icon) }}"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Display Sequence Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active)) class="w-4 h-4 rounded border-stone-300 text-[#233D32] focus:ring-[#233D32] accent-[#233D32]">
                    <span class="text-sm font-medium text-[#292D29]">Active (Visible in catalog filter)</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                <button type="submit" class="px-7 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                    Save Changes
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-3 text-stone-600 hover:text-[#233D32] text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
