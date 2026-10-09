<x-layouts.admin>
    <x-slot:heading>Homepage Carousel</x-slot:heading>
    <x-slot:subheading>Manage hero slides on the public homepage</x-slot:subheading>

    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.carousels.create') }}"
           class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
            + Add Slide
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($carousels as $carousel)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-stone-100 group">
                <div class="relative aspect-video bg-stone-100 overflow-hidden">
                    @if($carousel->image_path)
                        <img src="{{ asset('storage/'.$carousel->image_path) }}" alt="{{ $carousel->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl">🏔️</div>
                    @endif
                    @if(!$carousel->is_active)
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <span class="px-3 py-1 bg-stone-700 text-white text-xs font-semibold rounded-lg">Inactive</span>
                        </div>
                    @endif
                    <span class="absolute top-3 left-3 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center text-xs font-black text-stone-700">
                        {{ $carousel->sort_order ?: '—' }}
                    </span>
                </div>
                <div class="p-4">
                    <p class="font-bold text-stone-900 text-sm">{{ $carousel->title ?: '(no title)' }}</p>
                    @if($carousel->subtitle)
                        <p class="text-xs text-stone-500 mt-1 line-clamp-2">{{ $carousel->subtitle }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-3">
                        <a href="{{ route('admin.carousels.edit', $carousel) }}"
                           class="flex-1 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-medium rounded-lg text-center transition-colors">Edit</a>
                        <form method="POST" action="{{ route('admin.carousels.destroy', $carousel) }}"
                              onsubmit="return confirm('Delete this slide?')" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-20 text-center text-stone-400">
                <div class="text-5xl mb-3">🖼️</div>
                <p class="font-medium">No slides yet</p>
                <a href="{{ route('admin.carousels.create') }}" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">Add your first slide →</a>
            </div>
        @endforelse
    </div>
</x-layouts.admin>
