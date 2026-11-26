<x-layouts.admin>
    <x-slot:heading>Create Accommodation Unit</x-slot:heading>
    <x-slot:subheading>Add a new countryside villa, cottage, or suite to resort inventory</x-slot:subheading>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Identification</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Basic Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Room Number *</label>
                        <input type="text" name="room_number" value="{{ old('room_number') }}" required placeholder="e.g. DR-01"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-mono font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] @error('room_number') border-rose-400 bg-rose-50 @enderror">
                        @error('room_number')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Room Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Pine Breeze Cottage"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] @error('name') border-rose-400 bg-rose-50 @enderror">
                        @error('name')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Category *</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Booking Type *</label>
                        <select name="booking_type" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            <option value="both" {{ old('booking_type') === 'both' ? 'selected' : '' }}>Overnight & Day Pass</option>
                            <option value="overnight" {{ old('booking_type') === 'overnight' ? 'selected' : '' }}>Overnight Only</option>
                            <option value="day_pass" {{ old('booking_type') === 'day_pass' ? 'selected' : '' }}>Day Pass Only</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Accommodation Type *</label>
                        <select name="type" required class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            <option value="room" {{ old('type', 'room') === 'room' ? 'selected' : '' }}>Room / Suite</option>
                            <option value="cottage" {{ old('type') === 'cottage' ? 'selected' : '' }}>Cottage / Villa</option>
                        </select>
                        @error('type')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Description</label>
                        <textarea name="description" rows="3" placeholder="Describe the ambiance, views, and setting..." class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-light text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none leading-relaxed">{{ old('description') }}</textarea>
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
                        <input type="number" name="capacity_adults" value="{{ old('capacity_adults', 2) }}" min="1" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Children</label>
                        <input type="number" name="capacity_children" value="{{ old('capacity_children', 0) }}" min="0"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Base Price (₱) *</label>
                        <input type="number" name="base_price" value="{{ old('base_price', 2500) }}" min="0" step="0.01" required
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#233D32] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Floor Level</label>
                        <input type="number" name="floor_number" value="{{ old('floor_number', 1) }}" min="0"
                               class="w-full px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-bold text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div class="mt-4 p-4 bg-[#FAF8F4] rounded-xl border border-[#233D32]/10" x-data="{ allowsPets: {{ old('allows_pets') ? 'true' : 'false' }} }">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="allows_pets" value="1" {{ old('allows_pets') ? 'checked' : '' }}
                                   x-model="allowsPets" class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                            <span class="font-serif font-semibold text-[#233D32] text-xs">🐾 Allow Pets in this Accommodation</span>
                        </label>
                        <div x-show="allowsPets" class="flex-1">
                            <input type="number" name="pet_fee" value="{{ old('pet_fee', 300) }}" min="0" step="0.01"
                                   placeholder="Pet fee per night (₱)"
                                   class="w-full px-3.5 py-2 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        </div>
                    </div>
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
                                   {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}
                                   class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                            <span class="text-xs text-[#292D29] font-medium">{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Scoped Add-On Services with Popup Modal --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4"
                 x-data="{
                     addonModalOpen: false,
                     availableAddons: {{ Js::from($addons) }},
                     assignedAddons: [],
                     addAddon(addon) {
                         if (!this.assignedAddons.some(a => a.id === addon.id)) {
                             this.assignedAddons.push({
                                 id: addon.id,
                                 name: addon.name,
                                 price: addon.price,
                                 pricing_type: addon.pricing_type,
                                 description: addon.description,
                                 max_quantity: ''
                             });
                         }
                         this.addonModalOpen = false;
                     },
                     removeAddon(id) {
                         this.assignedAddons = this.assignedAddons.filter(a => a.id !== id);
                     },
                     isAssigned(id) {
                         return this.assignedAddons.some(a => a.id === id);
                     }
                 }">
                <div class="flex items-center justify-between border-b border-[#233D32]/10 pb-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Add-On Services</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Scoped Add-on Services</h3>
                        <p class="text-xs text-[#777A72] font-light mt-0.5">Assign add-ons available for this unit. Leave limit blank for unlimited.</p>
                    </div>
                    <button type="button" @click="addonModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#233D32]/10 text-[#233D32] hover:bg-[#233D32] hover:text-[#FAF8F4] rounded-xl text-xs font-serif font-bold transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        <span>Add Add-on Service</span>
                    </button>
                </div>

                <div x-show="assignedAddons.length > 0" class="divide-y divide-[#233D32]/10 border border-[#233D32]/10 rounded-xl overflow-hidden">
                    <template x-for="(assigned, index) in assignedAddons" :key="assigned.id">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-white hover:bg-[#FAF8F4]/50 gap-3">
                            <input type="hidden" :name="'addon_services[' + index + '][id]'" :value="assigned.id">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#233D32]/10 text-[#233D32] flex items-center justify-center font-serif font-bold text-xs"
                                     x-text="assigned.name.charAt(0)"></div>
                                <div>
                                    <p class="text-xs font-semibold text-[#233D32]" x-text="assigned.name"></p>
                                    <p class="text-[11px] text-[#777A72] font-light"
                                       x-text="'₱' + Number(assigned.price).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' · ' + (assigned.pricing_type || '').replace('_', ' ')"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] text-[#777A72] font-medium">Max Limit:</span>
                                    <input type="number" min="1" max="99"
                                           :name="'addon_services[' + index + '][max_quantity]'"
                                           x-model="assigned.max_quantity"
                                           placeholder="Unlimited"
                                           class="w-24 px-2.5 py-1.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-lg text-xs font-bold text-center text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] placeholder:font-normal placeholder:text-stone-400">
                                </div>
                                <button type="button" @click="removeAddon(assigned.id)"
                                        class="p-1.5 text-stone-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="Remove add-on">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="assignedAddons.length === 0" class="p-6 border-2 border-dashed border-[#233D32]/15 rounded-xl text-center space-y-2 bg-[#FAF8F4]/50">
                    <p class="text-xs text-[#777A72] font-light">No scoped add-on services assigned to this accommodation.</p>
                    <button type="button" @click="addonModalOpen = true" class="inline-flex items-center gap-1 text-xs font-bold text-[#233D32] hover:underline cursor-pointer">
                        + Click here to select and assign add-on services
                    </button>
                </div>

                {{-- Modal Overlay --}}
                <div x-show="addonModalOpen"
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
                     @keydown.escape.window="addonModalOpen = false">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#233D32]/10 space-y-4"
                         @click.outside="addonModalOpen = false">
                        <div class="flex items-center justify-between border-b border-[#233D32]/10 pb-3">
                            <div>
                                <h3 class="font-serif font-bold text-[#233D32] text-lg">Assign Add-On Service</h3>
                                <p class="text-xs text-[#777A72] font-light mt-0.5">Select an active service to enable for this room.</p>
                            </div>
                            <button type="button" @click="addonModalOpen = false" class="text-stone-400 hover:text-[#233D32] cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                            <template x-for="addon in availableAddons" :key="addon.id">
                                <div class="flex items-center justify-between p-3 rounded-xl border transition-colors"
                                     :class="isAssigned(addon.id) ? 'bg-[#233D32]/5 border-[#233D32]/30' : 'bg-white border-[#233D32]/10 hover:border-[#233D32]/30'">
                                    <div>
                                        <p class="text-xs font-semibold text-[#233D32]" x-text="addon.name"></p>
                                        <p class="text-[11px] text-[#777A72] font-light"
                                           x-text="'₱' + Number(addon.price).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' · ' + (addon.pricing_type || '').replace('_', ' ')"></p>
                                    </div>
                                    <div>
                                        <template x-if="isAssigned(addon.id)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold text-[#233D32] bg-[#233D32]/10">
                                                ✓ Added
                                            </span>
                                        </template>
                                        <template x-if="!isAssigned(addon.id)">
                                            <button type="button" @click="addAddon(addon)"
                                                    class="px-3.5 py-1.5 bg-[#233D32] hover:bg-[#1b2f27] text-[#FAF8F4] rounded-lg text-xs font-serif font-bold transition-colors cursor-pointer">
                                                + Select
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="pt-3 border-t border-[#233D32]/10 flex justify-end">
                            <button type="button" @click="addonModalOpen = false"
                                    class="px-4 py-2 border border-[#233D32]/20 text-[#233D32] rounded-xl text-xs font-medium hover:bg-[#FAF8F4] cursor-pointer">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photo Gallery Uploader --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10"
                 x-data="{
                     previews: [],
                     handleFiles(event) {
                         const files = Array.from(event.target.files);
                         this.previews = files.map(file => ({
                             name: file.name,
                             url: URL.createObjectURL(file),
                             size: (file.size / 1024).toFixed(1) + ' KB'
                         }));
                     },
                     clearPreviews() {
                         this.previews = [];
                         $refs.fileInput.value = '';
                     }
                 }">
                <div class="flex items-center justify-between mb-3 border-b border-[#233D32]/10 pb-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-0.5">Media Assets</span>
                        <h3 class="font-serif font-bold text-[#233D32] text-lg">Room Photos</h3>
                    </div>
                    <template x-if="previews.length > 0">
                        <span class="text-xs font-semibold text-[#233D32] bg-[#FAF8F4] border border-[#233D32]/15 px-3 py-1 rounded-xl"
                              x-text="previews.length + (previews.length === 1 ? ' photo selected' : ' photos selected')"></span>
                    </template>
                </div>
                <input type="file" name="photos[]" multiple accept="image/*" x-ref="fileInput" @change="handleFiles($event)"
                       class="w-full text-xs text-[#777A72] file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-[#233D32] file:text-[#F7F5EF] hover:file:bg-[#172A22] file:cursor-pointer cursor-pointer">
                <p class="text-[11px] text-[#777A72] mt-2 font-light">First selected photograph will be designated as the primary cover. Max 5MB per file.</p>

                {{-- Live Image Preview Grid --}}
                <div x-show="previews.length > 0" class="mt-4 pt-4 border-t border-[#233D32]/10">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-[#A68B61]">Selected Image Previews:</span>
                        <button type="button" @click="clearPreviews()" class="text-xs text-rose-700 hover:text-rose-900 font-semibold cursor-pointer">Clear selection</button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="relative bg-[#FAF8F4] rounded-xl overflow-hidden border border-[#233D32]/15 aspect-square group shadow-xs">
                                <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-[#172A22]/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2 text-[10px] text-white">
                                    <span class="truncate font-medium" x-text="preview.name"></span>
                                    <span class="text-[#A8B29A]" x-text="preview.size"></span>
                                </div>
                                <template x-if="index === 0">
                                    <span class="absolute top-1.5 left-1.5 bg-[#A68B61] text-[#172A22] text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">Cover</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Operational Status --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 space-y-4">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">State</span>
                <h3 class="font-serif font-bold text-[#233D32] text-lg border-b border-[#233D32]/10 pb-3">Operational Status</h3>
                <div class="flex flex-col sm:flex-row sm:items-center gap-6 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Initial Status</label>
                        <select name="status" class="px-3.5 py-2.5 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            @foreach(['available', 'occupied', 'maintenance', 'housekeeping', 'blocked'] as $s)
                                <option value="{{ $s }}" {{ old('status', 'available') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer sm:pt-6">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                        <span class="text-xs font-semibold text-[#233D32]">Active (Publicly bookable on search catalog)</span>
                    </label>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                    @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-8 py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_15px_rgba(35,61,50,0.18)]">
                    Create Accommodation
                </button>
                <a href="{{ route('admin.rooms.index') }}" class="px-6 py-3.5 border border-[#233D32]/15 hover:bg-white text-[#777A72] hover:text-[#233D32] rounded-xl text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>