<?php

use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public ?int $categoryId = null;
    public string $statusFilter = '';
    public string $typeFilter = '';
    public ?string $feedbackMessage = null;

    public function updateRoomStatus(int $roomId, string $newStatus): void
    {
        $room = Room::find($roomId);
        if ($room && in_array($newStatus, ['available', 'occupied', 'maintenance', 'housekeeping', 'out_of_service', 'blocked'])) {
            $room->update(['status' => $newStatus]);
            $this->feedbackMessage = "Room {$room->room_number} status updated to ".ucfirst(str_replace('_', ' ', $newStatus)).".";
        }
    }

    public function getCategoriesProperty(): EloquentCollection
    {
        return RoomCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getRoomsProperty(): EloquentCollection
    {
        return Room::with(['category'])
            ->when(filled($this->search), function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('room_number', 'like', $term);
                });
            })
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when(filled($this->statusFilter), fn ($q) => $q->where('status', $this->statusFilter))
            ->when(filled($this->typeFilter), fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('room_number')
            ->get();
    }
}; ?>

<div>
    {{-- Search and Filter Controls --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 bg-white p-4 rounded-2xl border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)]">
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative min-w-56">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search room # or name..."
                       class="w-full pl-9 pr-4 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                <svg class="w-4 h-4 text-[#777A72] absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select wire:model.live="categoryId"
                    class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                <option value="">All Categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                    class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                <option value="">All Operational Statuses</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="housekeeping">Housekeeping</option>
                <option value="maintenance">Maintenance</option>
                <option value="out_of_service">Out of Service</option>
                <option value="blocked">Blocked</option>
            </select>

            <select wire:model.live="typeFilter"
                    class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                <option value="">All Accommodation Types</option>
                <option value="room">Rooms</option>
                <option value="cottage">Cottages</option>
            </select>
        </div>

        <a href="{{ route('admin.rooms.create') }}"
           class="px-4 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-widest rounded-xl transition-all shadow-xs self-start sm:self-auto">
            + New Room Unit
        </a>
    </div>

    @if($feedbackMessage)
        <div class="mb-4 p-3 bg-[#FAF8F4] border border-[#233D32]/20 text-[#233D32] rounded-xl text-xs font-semibold flex items-center justify-between shadow-2xs">
            <span>✓ {{ $feedbackMessage }}</span>
            <button wire:click="$set('feedbackMessage', null)" class="text-[#777A72] hover:text-[#233D32] font-bold">✕</button>
        </div>
    @endif

    {{-- Rooms Inventory Table --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                    <tr>
                        @foreach(['Unit #', 'Room Name', 'Type', 'Category', 'Capacity', 'Rate / Night', 'Status', 'Pet Policy', 'Actions'] as $h)
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold text-[#777A72] uppercase tracking-[0.14em]">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#233D32]/5">
                    @forelse($this->rooms as $room)
                        <tr class="hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="px-4 py-4 font-mono font-bold text-[#233D32] text-sm">#{{ $room->room_number }}</td>
                            <td class="px-4 py-4">
                                <div class="font-serif font-bold text-sm text-[#233D32]">{{ $room->name }}</div>
                                @if(!$room->is_active)
                                    <span class="text-[10px] text-stone-400 uppercase tracking-wider">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $room->type === 'cottage' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-emerald-100 text-emerald-900 border border-emerald-300' }}">
                                    {{ $room->type === 'cottage' ? 'Cottage' : 'Room' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-[#595D58] font-medium">{{ $room->category?->name }}</td>
                            <td class="px-4 py-4 text-[#777A72]">
                                {{ $room->capacity_adults }} Adults @if($room->capacity_children > 0)· {{ $room->capacity_children }} Children @endif
                            </td>
                            <td class="px-4 py-4 font-serif font-bold text-sm text-[#233D32]">₱{{ number_format($room->base_price, 0) }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-emerald-50 text-emerald-900 border-emerald-300',
                                        'occupied' => 'bg-[#233D32] text-[#F7F5EF] border-[#172A22]',
                                        'maintenance' => 'bg-amber-50 text-amber-900 border-amber-300',
                                        'housekeeping' => 'bg-[#EFEBE1] text-[#78866B] border-[#A8B29A]',
                                        'out_of_service' => 'bg-stone-200 text-stone-800 border-stone-300',
                                        'blocked' => 'bg-stone-300 text-stone-700 border-stone-400',
                                    ];
                                @endphp
                                <select wire:change="updateRoomStatus({{ $room->id }}, $event.target.value)"
                                        class="text-[11px] font-semibold uppercase tracking-wider rounded-lg border py-1.5 pl-2.5 pr-7 cursor-pointer transition-colors focus:ring-2 focus:ring-[#233D32]/20 {{ $statusColors[$room->status] ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                                    <option value="available" @selected($room->status === 'available')>Available</option>
                                    <option value="occupied" @selected($room->status === 'occupied')>Occupied</option>
                                    <option value="maintenance" @selected($room->status === 'maintenance')>Maintenance</option>
                                    <option value="housekeeping" @selected($room->status === 'housekeeping')>Housekeeping</option>
                                    <option value="out_of_service" @selected($room->status === 'out_of_service')>Out of Service</option>
                                    <option value="blocked" @selected($room->status === 'blocked')>Blocked</option>
                                </select>
                            </td>
                            <td class="px-4 py-4">
                                @if($room->allows_pets)
                                    <span class="text-xs text-[#A68B61] font-semibold" title="Pet-friendly — ₱{{ number_format($room->pet_fee, 0) }}/night">🐾 Allowed</span>
                                @else
                                    <span class="text-stone-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.rooms.show', $room) }}"
                                       class="px-2.5 py-1 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-[11px] font-semibold rounded-lg transition-colors">View</a>
                                    <a href="{{ route('admin.rooms.edit', $room) }}"
                                       class="px-2.5 py-1 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-[11px] font-semibold rounded-lg transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                          onsubmit="return confirm('Delete room {{ $room->room_number }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 border border-rose-200 text-rose-700 hover:bg-rose-50 text-[11px] font-semibold rounded-lg transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center text-[#777A72]">
                                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h3 class="font-serif font-bold text-base text-[#233D32]">No matching rooms found</h3>
                                <p class="text-xs text-[#777A72] mt-1 font-light">Adjust your search term or category filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>