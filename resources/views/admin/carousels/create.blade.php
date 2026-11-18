<x-layouts.admin>
    <x-slot:heading>Add Hero Slide</x-slot:heading>
    <x-slot:subheading>Upload a high-resolution countryside photograph for the homepage gallery</x-slot:subheading>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.carousels.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80 space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">
                        Slide Photograph <span class="text-stone-400 font-normal normal-case">(High resolution, max 8MB)</span> *
                    </label>
                    <input type="file" name="image" required accept="image/*"
                           class="w-full text-sm text-[#292D29] file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-[#EFEBE1] file:text-[#233D32] hover:file:bg-[#233D32] hover:file:text-white file:transition-colors">
                    @error('image') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Editorial Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Find Your Quiet Among Nature"
                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Supporting Narrative / Subtitle</label>
                    <textarea name="subtitle" rows="2" placeholder="Discover a countryside escape where nature, comfort, and memorable experiences come together."
                              class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none">{{ old('subtitle') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Action Button Label</label>
                        <input type="text" name="cta_text" value="{{ old('cta_text') }}" placeholder="e.g. Explore Suites"
                               class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Action Destination URL</label>
                        <input type="url" name="cta_url" value="{{ old('cta_url', url('/rooms')) }}"
                               class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Sequence Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                               class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-stone-300 text-[#233D32] focus:ring-[#233D32] accent-[#233D32]">
                        <span class="text-sm font-medium text-[#292D29]">Publish to homepage carousel</span>
                    </label>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                    @foreach($errors->all() as $error)<p class="text-xs text-rose-700 font-medium">• {{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="px-7 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                    Upload Slide
                </button>
                <a href="{{ route('admin.carousels.index') }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-xs font-semibold uppercase tracking-wider hover:bg-stone-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
