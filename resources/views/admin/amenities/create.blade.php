<x-layouts.admin>
    <x-slot:heading>Add Resort Amenity</x-slot:heading>
    <x-slot:subheading>Catalog a new amenity for resort facilities and accommodation suites</x-slot:subheading>

    <div class="max-w-xl bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80">
        <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Amenity Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Natural Stone Dipping Pool, High-Speed Starlink"
                       class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                @error('name') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Icon Representation</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. 🏊 or 📶"
                       class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                <p class="text-xs text-stone-400 mt-1.5">Single emoji symbol or identifier icon</p>
                @error('icon') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                <button type="submit" class="px-7 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                    Create Amenity
                </button>
                <a href="{{ route('admin.amenities.index') }}" class="px-5 py-3 text-stone-600 hover:text-[#233D32] text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
