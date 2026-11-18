<x-layouts.admin>
    <x-slot:heading>Resort Amenities</x-slot:heading>
    <x-slot:subheading>Manage resort and room amenities catalogue</x-slot:subheading>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                ← General Settings
            </a>
            <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
                Room Categories
            </a>
        </div>
        <a href="{{ route('admin.amenities.create') }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-medium uppercase tracking-wider rounded-xl transition-colors shadow-xs">
            + Add Amenity
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-stone-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#FAF8F4] border-b border-stone-200/80 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-6 py-3.5">Icon</th>
                        <th class="px-6 py-3.5">Amenity Name</th>
                        <th class="px-6 py-3.5">Linked Rooms</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($amenities as $amenity)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-6 py-4 text-xl">{{ $amenity->icon ?: '✨' }}</td>
                            <td class="px-6 py-4 font-serif font-bold text-[#172A22] text-base">{{ $amenity->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#EFEBE1] text-[#233D32]">
                                    {{ $amenity->rooms_count }} {{ Str::plural('suite', $amenity->rooms_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.amenities.edit', $amenity) }}"
                                       class="px-3 py-1.5 bg-[#EFEBE1] hover:bg-[#233D32] text-[#233D32] hover:text-[#F7F5EF] text-xs font-medium rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.amenities.destroy', $amenity) }}"
                                          onsubmit="return confirm('Delete amenity {{ $amenity->name }}?')">
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
                            <td colspan="4" class="px-6 py-12 text-center text-stone-400 font-serif text-sm">
                                No resort amenities catalogued yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($amenities->hasPages())
            <div class="px-6 py-4 border-t border-stone-200/80 bg-[#FAF8F4]">{{ $amenities->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
