<x-layouts.admin>
    <x-slot:heading>Rooms</x-slot:heading>
    <x-slot:subheading>Manage all room inventory</x-slot:subheading>

    <div class="flex items-center justify-between mb-6">
        <div></div>
        <a href="{{ route('admin.rooms.create') }}"
           class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
            + Add Room
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        @foreach(['Room #', 'Name', 'Category', 'Capacity', 'Price', 'Status', 'Pets', 'Actions'] as $h)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-4 py-4 font-mono font-bold text-stone-800">{{ $room->room_number }}</td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-stone-900">{{ $room->name }}</div>
                                @if(!$room->is_active)
                                    <span class="text-xs text-stone-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-stone-600">{{ $room->category?->name }}</td>
                            <td class="px-4 py-4 text-stone-600">
                                {{ $room->capacity_adults }}A @if($room->capacity_children > 0){{ $room->capacity_children }}C @endif
                            </td>
                            <td class="px-4 py-4 font-semibold text-stone-900">₱{{ number_format($room->base_price, 0) }}/night</td>
                            <td class="px-4 py-4">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-emerald-100 text-emerald-700',
                                        'occupied' => 'bg-red-100 text-red-700',
                                        'maintenance' => 'bg-stone-200 text-stone-700',
                                        'housekeeping' => 'bg-purple-100 text-purple-700',
                                        'blocked' => 'bg-stone-300 text-stone-600',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusColors[$room->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ ucfirst($room->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($room->allows_pets)
                                    <span class="text-lg" title="Pet-friendly — ₱{{ number_format($room->pet_fee, 0) }}/night">🐾</span>
                                @else
                                    <span class="text-stone-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.rooms.show', $room) }}"
                                       class="px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium rounded-lg transition-colors">View</a>
                                    <a href="{{ route('admin.rooms.edit', $room) }}"
                                       class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-medium rounded-lg transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                          onsubmit="return confirm('Delete room {{ $room->room_number }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center text-stone-400">
                                <div class="text-4xl mb-3">🏨</div>
                                <p class="font-medium">No rooms yet</p>
                                <a href="{{ route('admin.rooms.create') }}" class="mt-2 inline-block text-sm text-emerald-600 font-medium hover:underline">Add your first room →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rooms->hasPages())
            <div class="px-4 py-4 border-t border-stone-100">{{ $rooms->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
