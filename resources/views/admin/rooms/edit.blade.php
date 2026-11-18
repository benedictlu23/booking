<x-layouts.admin>
    <x-slot:heading>Edit Accommodation Unit</x-slot:heading>
    <x-slot:subheading>Unit #{{ $room->room_number }} — {{ $room->name }}</x-slot:subheading>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.rooms.update', $room) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Identification</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Basic Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Room Number *</label>
                        <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-mono font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Room Name *</label>
                        <input type="text" name="name" value="{{ old('name', $room->name) }}" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Category *</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $room->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Booking Type *</label>
                        <select name="booking_type" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            @foreach(['both' => 'Overnight & Day Pass', 'overnight' => 'Overnight Only', 'day_pass' => 'Day Pass Only'] as $val => $label)
                                <option value="{{ $val }}" {{ old('booking_type', $room->booking_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none leading-relaxed">{{ old('description', $room->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Capacity & Pricing --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Specifications</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Capacity & Rates</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Adults *</label>
                        <input type="number" name="capacity_adults" value="{{ old('capacity_adults', $room->capacity_adults) }}" min="1" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Children</label>
                        <input type="number" name="capacity_children" value="{{ old('capacity_children', $room->capacity_children) }}" min="0"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Base Price (₱) *</label>
                        <input type="number" name="base_price" value="{{ old('base_price', $room->base_price) }}" min="0" step="0.01" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#233D32] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Status *</label>
                        <select name="status" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            @foreach(['available', 'occupied', 'maintenance', 'housekeeping', 'blocked'] as $s)
                                <option value="{{ $s }}" {{ old('status', $room->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-[#FAF8F4] rounded-xl border border-[#233D32]/10">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="allows_pets" value="1" {{ old('allows_pets', $room->allows_pets) ? 'checked' : '' }}
                               class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                        <span class="font-serif font-semibold text-[#233D32] text-xs">🐾 Allow Pets</span>
                    </label>
                    <input type="number" name="pet_fee" value="{{ old('pet_fee', $room->pet_fee) }}" min="0" step="0.01"
                           placeholder="Pet fee per night (₱)"
                           class="flex-1 px-3.5 py-2 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                               class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                        <span class="text-xs font-semibold text-[#233D32]">Active (Visible)</span>
                    </label>
                </div>
            </div>

            {{-- Amenities --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Features</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Amenities Included</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-1">
                    @foreach($amenities as $amenity)
                        <label class="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-xl border border-[#233D32]/10 hover:bg-[#FAF8F4] transition-colors">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                   {{ $room->amenities->contains($amenity->id) ? 'checked' : '' }}
                                   class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                            <span class="text-xs text-[#292D29] font-medium">{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Room Photos Manager --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10">
                <div class="flex items-center justify-between mb-4 border-b border-[#233D32]/10 pb-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Media Assets</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Room Gallery Photos</h3>
                    </div>
                    @if($room->photos->isNotEmpty())
                        <span class="px-3 py-1 bg-[#FAF8F4] border border-[#233D32]/10 text-[#233D32] rounded-xl text-xs font-semibold">
                            {{ $room->photos->count() }} {{ Str::plural('photo', $room->photos->count()) }}
                        </span>
                    @endif
                </div>

                {{-- Existing photos grid --}}
                @if($room->photos->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 mb-6">
                        @foreach($room->photos->unique('path') as $photo)
                            <div class="relative bg-[#FAF8F4] rounded-2xl p-2 border border-[#233D32]/10 flex flex-col items-center shadow-2xs hover:shadow-sm transition-shadow">
                                <div class="relative w-full h-24 rounded-xl overflow-hidden bg-stone-200">
                                    <img src="{{ asset('storage/'.$photo->path) }}" alt="Room photo"
                                         class="w-full h-full object-cover">

                                    {{-- Persistent Red ✕ Badge Button --}}
                                    <button type="button"
                                            onclick="if(confirm('Are you sure you want to remove this photo?')) document.getElementById('del-photo-{{ $photo->id }}').submit()"
                                            title="Remove this photo"
                                            class="absolute top-1.5 right-1.5 w-6 h-6 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white rounded-full text-xs font-bold flex items-center justify-center shadow-md ring-2 ring-white cursor-pointer transition-transform hover:scale-110 z-10">
                                        ✕
                                    </button>

                                    @if($photo->is_cover)
                                        <span class="absolute bottom-1.5 left-1.5 px-2 py-0.5 bg-[#A68B61] text-[#172A22] text-[9px] font-bold rounded-md shadow-xs">
                                            Cover
                                        </span>
                                    @endif
                                </div>

                                {{-- Explicit Text Action Button --}}
                                <button type="button"
                                        onclick="if(confirm('Are you sure you want to remove this photo?')) document.getElementById('del-photo-{{ $photo->id }}').submit()"
                                        class="w-full mt-2 py-1 text-[10px] uppercase tracking-wider font-semibold text-rose-700 hover:text-white hover:bg-rose-600 border border-rose-200 hover:border-rose-600 rounded-lg transition-colors flex items-center justify-center gap-1 cursor-pointer">
                                    <span>✕</span>
                                    <span>Remove</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 mb-6 rounded-2xl bg-[#FAF8F4] border border-dashed border-[#233D32]/15 text-center text-xs text-[#777A72] font-light">
                        No photos currently uploaded for this accommodation.
                    </div>
                @endif

                {{-- Upload new photos with dynamic live preview --}}
                <div class="p-4 bg-[#FAF8F4] border border-[#233D32]/10 rounded-2xl"
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
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-[#233D32] uppercase tracking-wider">
                            Upload Additional Photos
                        </label>
                        <template x-if="newPreviews.length > 0">
                            <span class="text-xs font-semibold text-[#233D32] bg-white px-2.5 py-0.5 rounded-lg border border-[#233D32]/10"
                                  x-text="newPreviews.length + (newPreviews.length === 1 ? ' new photo selected' : ' new photos selected')"></span>
                        </template>
                    </div>
                    <input type="file" name="photos[]" multiple accept="image/*" x-ref="fileInput" @change="handleFiles($event)"
                           class="w-full text-xs text-[#777A72] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-[#233D32] file:text-[#F7F5EF] hover:file:bg-[#172A22] file:cursor-pointer cursor-pointer">
                    <p class="text-[11px] text-[#777A72] mt-2 font-light">
                        Supported formats: JPG, PNG, WEBP, AVIF.
                    </p>

                    {{-- Live Image Preview Grid --}}
                    <div x-show="newPreviews.length > 0" class="mt-4 pt-4 border-t border-[#233D32]/10">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-[#A68B61]">New Photos to Upload:</span>
                            <button type="button" @click="clearPreviews()" class="text-xs text-rose-700 hover:text-rose-900 font-semibold cursor-pointer">Clear selection</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                            <template x-for="(preview, index) in newPreviews" :key="index">
                                <div class="relative bg-white rounded-xl overflow-hidden border border-[#233D32]/10 aspect-square group shadow-xs">
                                    <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-[#172A22]/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2 text-[10px] text-white">
                                        <span class="truncate font-medium" x-text="preview.name"></span>
                                        <span class="text-[#A8B29A]" x-text="preview.size"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                    @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-8 py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_15px_rgba(35,61,50,0.18)]">
                    Save Changes
                </button>
                <a href="{{ route('admin.rooms.show', $room) }}" class="px-6 py-3.5 border border-[#233D32]/15 hover:bg-white text-[#777A72] hover:text-[#233D32] rounded-xl text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>

        {{-- Standalone photo delete forms (outside main form to prevent nesting) --}}
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