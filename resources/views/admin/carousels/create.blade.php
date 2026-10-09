<x-layouts.admin>
    <x-slot:heading>Add Carousel Slide</x-slot:heading>
    <x-slot:subheading>Upload a new hero image for the homepage</x-slot:subheading>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.carousels.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Slide Image * <span class="text-xs text-stone-400">(max 8MB)</span></label>
                    <input type="file" name="image" required accept="image/*"
                           class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Your Nature Escape Awaits"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Subtitle</label>
                    <textarea name="subtitle" rows="2" placeholder="Short supporting description"
                              class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('subtitle') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Button Text</label>
                        <input type="text" name="cta_text" value="{{ old('cta_text') }}" placeholder="e.g. Book Now"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Button URL</label>
                        <input type="url" name="cta_url" value="{{ old('cta_url', url('/rooms')) }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-stone-700">Show on homepage</span>
                </label>
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl">
                    @foreach($errors->all() as $error)<p class="text-sm text-red-700">• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-emerald-200">Upload Slide</button>
                <a href="{{ route('admin.carousels.index') }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-sm hover:bg-stone-50">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
