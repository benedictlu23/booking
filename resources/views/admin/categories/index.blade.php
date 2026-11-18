<x-layouts.admin>
    <x-slot:heading>Room Categories</x-slot:heading>
    <x-slot:subheading>Manage accommodation tiers and villa classifications</x-slot:subheading>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                ← General Settings
            </a>
            <a href="{{ route('admin.amenities.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                Resort Amenities
            </a>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-medium uppercase tracking-wider rounded-xl transition-colors shadow-xs">
            + Add Category
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-stone-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#FAF8F4] border-b border-stone-200/80 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-6 py-3.5">Category Name</th>
                        <th class="px-6 py-3.5">Slug Identifier</th>
                        <th class="px-6 py-3.5">Suites</th>
                        <th class="px-6 py-3.5">Display Order</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-serif font-bold text-[#172A22] text-base block">{{ $category->name }}</span>
                                @if($category->description)
                                    <p class="text-xs text-stone-500 font-normal mt-0.5 truncate max-w-xs">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-stone-500">{{ $category->slug }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#EFEBE1] text-[#233D32]">
                                    {{ $category->rooms_count }} {{ Str::plural('suite', $category->rooms_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-stone-600">{{ $category->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $category->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
                                    {{ $category->is_active ? 'Active' : 'Archived' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="px-3 py-1.5 bg-[#EFEBE1] hover:bg-[#233D32] text-[#233D32] hover:text-[#F7F5EF] text-xs font-medium rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Delete category {{ $category->name }}?')">
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
                                No room categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-stone-200/80 bg-[#FAF8F4]">{{ $categories->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
