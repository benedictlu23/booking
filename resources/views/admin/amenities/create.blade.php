<x-layouts.admin>
    <x-slot:heading>Add Resort Amenity</x-slot:heading>
    <x-slot:subheading>Define a room or resort amenity</x-slot:subheading>

    <div class="max-w-xl bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
        <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Amenity Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Swimming Pool, Free Wi-Fi"
                       class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Icon / Emoji</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. 🏊 or 📶"
                       class="w-full px-3.5 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-stone-400 mt-1">Single emoji or icon identifier</p>
                @error('icon') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Create Amenity
                </button>
                <a href="{{ route('admin.amenities.index') }}" class="px-4 py-2.5 text-stone-600 hover:text-stone-900 text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
