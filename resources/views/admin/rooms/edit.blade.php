<x-layouts.admin>
    <x-slot:heading>Edit Room</x-slot:heading>
    <x-slot:subheading>{{ $room->room_number }} — {{ $room->name }}</x-slot:subheading>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.rooms.update', $room) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Room Number *</label>
                        <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Room Name *</label>
                        <input type="text" name="name" value="{{ old('name', $room->name) }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Category *</label>
                        <select name="category_id" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $room->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Booking Type *</label>
                        <select name="booking_type" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach(['both' => 'Overnight & Day Pass', 'overnight' => 'Overnight Only', 'day_pass' => 'Day Pass Only'] as $val => $label)
                                <option value="{{ $val }}" {{ old('booking_type', $room->booking_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('description', $room->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Capacity & Pricing</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Adults *</label>
                        <input type="number" name="capacity_adults" value="{{ old('capacity_adults', $room->capacity_adults) }}" min="1" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Children</label>
                        <input type="number" name="capacity_children" value="{{ old('capacity_children', $room->capacity_children) }}" min="0"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Base Price (₱) *</label>
                        <input type="number" name="base_price" value="{{ old('base_price', $room->base_price) }}" min="0" step="0.01" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Status *</label>
                        <select name="status" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach(['available', 'occupied', 'maintenance', 'housekeeping', 'blocked'] as $s)
                                <option value="{{ $s }}" {{ old('status', $room->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allows_pets" value="1" {{ old('allows_pets', $room->allows_pets) ? 'checked' : '' }}
                               class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="font-medium text-stone-900 text-sm">🐾 Allow Pets</span>
                    </label>
                    <input type="number" name="pet_fee" value="{{ old('pet_fee', $room->pet_fee) }}" min="0" step="0.01"
                           placeholder="Pet fee per night (₱)"
                           class="flex-1 px-3 py-2 border border-amber-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                               class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-medium text-stone-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Amenities</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($amenities as $amenity)
                        <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-stone-50 transition-colors">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                   {{ $room->amenities->contains($amenity->id) ? 'checked' : '' }}
                                   class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-stone-700">{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-2">Add More Photos</h3>
                <p class="text-xs text-stone-400 mb-3">Current: {{ $room->photos->count() }} photo(s)</p>
                <input type="file" name="photos[]" multiple accept="image/*"
                       class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl">
                    @foreach($errors->all() as $error)<p class="text-sm text-red-700">• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.rooms.show', $room) }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-sm hover:bg-stone-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
