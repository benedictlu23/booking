<x-layouts.admin>
    <x-slot:heading>Room Categories</x-slot:heading>
    <x-slot:subheading>Manage room categories and tiers</x-slot:subheading>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-stone-600 bg-white border border-stone-200 hover:bg-stone-50">← General Settings</a>
            <a href="{{ route('admin.amenities.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-stone-600 bg-white border border-stone-200 hover:bg-stone-50">Resort Amenities</a>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
            + Add Category
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Slug</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Rooms</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Sort Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($categories as $category)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-4 py-4 font-semibold text-stone-900">
                                {{ $category->name }}
                                @if($category->description)
                                    <p class="text-xs text-stone-400 font-normal mt-0.5 truncate max-w-xs">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 font-mono text-xs text-stone-500">{{ $category->slug }}</td>
                            <td class="px-4 py-4 text-xs font-semibold text-stone-700">{{ $category->rooms_count }} rooms</td>
                            <td class="px-4 py-4 text-xs text-stone-500">{{ $category->sort_order }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Delete category {{ $category->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold rounded-lg transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-stone-400 text-sm">
                                No categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="px-4 py-3 border-t border-stone-100">{{ $categories->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
