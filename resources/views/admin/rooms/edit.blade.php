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
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-bold text-stone-900">Room Photos</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Manage existing photos or add new ones. Click the red ✕ or "Remove" button to delete any photo.</p>
                    </div>
                    @if($room->photos->isNotEmpty())
                        <span class="px-2.5 py-1 bg-stone-100 text-stone-700 rounded-lg text-xs font-bold">
                            {{ $room->photos->count() }} {{ Str::plural('photo', $room->photos->count()) }}
                        </span>
                    @endif
                </div>

                {{-- Existing photos --}}
                @if($room->photos->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 mb-6">
                        @foreach($room->photos->unique('path') as $photo)
                            <div class="relative bg-stone-50 rounded-2xl p-2 border border-stone-200 flex flex-col items-center shadow-xs hover:shadow-md transition-shadow">
                                <div class="relative w-full h-24 rounded-xl overflow-hidden bg-stone-200">
                                    <img src="{{ asset('storage/'.$photo->path) }}" alt="Room photo"
                                         class="w-full h-full object-cover">

                                    {{-- Persistent, high-contrast Red ✕ Badge Button (always visible) --}}
                                    <button type="button"
                                            onclick="if(confirm('Are you sure you want to remove this photo?')) document.getElementById('del-photo-{{ $photo->id }}').submit()"
                                            title="Remove this photo"
                                            class="absolute top-1.5 right-1.5 w-7 h-7 bg-red-600 hover:bg-red-700 active:scale-95 text-white rounded-full text-xs font-black flex items-center justify-center shadow-md ring-2 ring-white cursor-pointer transition-transform hover:scale-110 z-10">
                                        ✕
                                    </button>

                                    @if($photo->is_cover)
                                        <span class="absolute bottom-1.5 left-1.5 px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-bold rounded-full shadow">
                                            Cover
                                        </span>
                                    @endif
                                </div>

                                {{-- Explicit Text Action Button --}}
                                <button type="button"
                                        onclick="if(confirm('Are you sure you want to remove this photo?')) document.getElementById('del-photo-{{ $photo->id }}').submit()"
                                        class="w-full mt-2 py-1 text-[11px] font-bold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-colors flex items-center justify-center gap-1 cursor-pointer">
                                    <span>✕</span>
                                    <span>Remove</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 mb-6 rounded-2xl bg-stone-50 border border-dashed border-stone-200 text-center text-xs text-stone-400">
                        📷 No photos uploaded for this room yet. Add your first photo below!
                    </div>
                @endif

                {{-- Upload new photos with dynamic live preview --}}
                <div class="p-4 bg-stone-50/70 border border-stone-200 rounded-2xl"
                     x-data="{
                         newPreviews: [],
                         handleFiles(event) {
                             const files = Array.from(event.target.files);
                             this.newPreviews = files.map(file => ({
                                 name: file.name,
                                 url: URL.createObjectURL(file),
                                 size: (file.size / 1024).toFixed(1) + ' KB'
                             }));
                         },
                         clearPreviews() {
                             this.newPreviews = [];
                             $refs.fileInput.value = '';
                         }
                     }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider">
                            ➕ Upload New Photos
                        </label>
                        <template x-if="newPreviews.length > 0">
                            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg"
                                  x-text="newPreviews.length + (newPreviews.length === 1 ? ' new photo selected' : ' new photos selected')"></span>
                        </template>
                    </div>
                    <input type="file" name="photos[]" multiple accept="image/*" x-ref="fileInput" @change="handleFiles($event)"
                           class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer cursor-pointer">
                    <p class="text-[11px] text-stone-400 mt-2">
                        Supported formats: JPG, PNG, WEBP, AVIF. You can select multiple images at once.
                    </p>

                    {{-- Live Image Preview Grid --}}
                    <div x-show="newPreviews.length > 0" class="mt-4 pt-4 border-t border-stone-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-stone-700 uppercase tracking-wide">New Photos to Upload:</span>
                            <button type="button" @click="clearPreviews()" class="text-xs text-red-500 hover:text-red-700 font-semibold cursor-pointer">Clear selection</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                            <template x-for="(preview, index) in newPreviews" :key="index">
                                <div class="relative bg-white rounded-xl overflow-hidden border border-stone-200 aspect-square group shadow-xs">
                                    <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2 text-[10px] text-white">
                                        <span class="truncate font-medium" x-text="preview.name"></span>
                                        <span class="text-stone-300" x-text="preview.size"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
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

        {{-- Standalone photo delete forms (outside the main form so they don't nest) --}}
        @foreach($room->photos as $photo)
            <form id="del-photo-{{ $photo->id }}"
                  method="POST"
                  action="{{ route('admin.rooms.photos.destroy', [$room, $photo]) }}"
                  style="display:none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</x-layouts.admin>
