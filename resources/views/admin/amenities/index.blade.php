<x-layouts.admin>
    <x-slot:heading>Resort Amenities</x-slot:heading>
    <x-slot:subheading>Manage resort and room amenities</x-slot:subheading>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-stone-600 bg-white border border-stone-200 hover:bg-stone-50">← General Settings</a>
            <a href="{{ route('admin.categories.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-stone-600 bg-white border border-stone-200 hover:bg-stone-50">Room Categories</a>
        </div>
        <a href="{{ route('admin.amenities.create') }}"
           class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
            + Add Amenity
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Icon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Amenity Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Linked Rooms</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($amenities as $amenity)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-4 py-4 text-xl">{{ $amenity->icon ?: '✨' }}</td>
                            <td class="px-4 py-4 font-semibold text-stone-900">{{ $amenity->name }}</td>
                            <td class="px-4 py-4 text-xs font-semibold text-stone-700">{{ $amenity->rooms_count }} rooms</td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.amenities.edit', $amenity) }}"
                                       class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('admin.amenities.destroy', $amenity) }}"
                                          onsubmit="return confirm('Delete amenity {{ $amenity->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold rounded-lg transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-stone-400 text-sm">
                                No amenities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($amenities->hasPages())
            <div class="px-4 py-3 border-t border-stone-100">{{ $amenities->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
