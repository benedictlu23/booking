<x-layouts.admin>
    <x-slot:heading>Edit Category: {{ $category->name }}</x-slot:heading>
    <x-slot:subheading>Update room category details</x-slot:subheading>

    <div class="max-w-xl bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Category Name</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('description', $category->description) }}</textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Icon / Emoji</label>
                    <input type="text" name="icon" value="{{ old('icon', $category->icon) }}"
                           class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                           class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active)) class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm text-stone-700">Active (Visible in booking filter)</label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 text-stone-600 hover:text-stone-900 text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
