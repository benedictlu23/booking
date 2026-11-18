<x-layouts.admin>
    <x-slot:heading>Homepage Hero Media & CMS</x-slot:heading>
    <x-slot:subheading>Manage editorial hero slides, call-to-actions, and featured imagery for the public resort homepage</x-slot:subheading>

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.carousels.create') }}"
           class="px-5 py-2.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-widest rounded-xl transition-all shadow-xs">
            + Add Hero Slide
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($carousels as $carousel)
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 group flex flex-col justify-between">
                <div>
                    <div class="relative aspect-video bg-[#FAF8F4] overflow-hidden">
                        @if($carousel->image_path)
                            <img src="{{ asset('storage/'.$carousel->image_path) }}" alt="{{ $carousel->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-4xl opacity-40">🌿</div>
                        @endif
                        @if(!$carousel->is_active)
                            <div class="absolute inset-0 bg-[#172A22]/60 backdrop-blur-2xs flex items-center justify-center">
                                <span class="px-3 py-1 bg-white text-[#233D32] text-[10px] uppercase tracking-wider font-semibold rounded-lg">Draft / Inactive</span>
                            </div>
                        @endif
                        <span class="absolute top-3 left-3 px-2 py-0.5 bg-[#172A22]/80 backdrop-blur-xs text-[#F7F5EF] rounded-md text-[10px] font-mono font-bold">
                            #{{ $carousel->sort_order ?: '1' }}
                        </span>
                    </div>
                    <div class="p-5">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Slide Title</span>
                        <p class="font-serif font-bold text-[#233D32] text-base leading-snug">{{ $carousel->title ?: '(Untitled Slide)' }}</p>
                        @if($carousel->subtitle)
                            <p class="text-xs text-[#777A72] mt-1 line-clamp-2 font-light leading-relaxed">{{ $carousel->subtitle }}</p>
                        @endif
                    </div>
                </div>

                <div class="p-5 pt-0">
                    <div class="flex items-center gap-2 pt-3 border-t border-[#233D32]/10">
                        <a href="{{ route('admin.carousels.edit', $carousel) }}"
                           class="flex-1 py-2 bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/15 text-xs font-semibold uppercase tracking-wider rounded-xl text-center transition-colors">
                            Edit Slide
                        </a>
                        <form method="POST" action="{{ route('admin.carousels.destroy', $carousel) }}"
                              onsubmit="return confirm('Delete this hero slide?')" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full py-2 border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center text-[#777A72] bg-white rounded-2xl border border-[#233D32]/10">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-serif font-bold text-base text-[#233D32]">No Hero Slides Configured</h3>
                <a href="{{ route('admin.carousels.create') }}" class="mt-2 inline-block text-xs uppercase tracking-wider font-semibold text-[#A68B61] hover:underline">Add your first slide →</a>
            </div>
        @endforelse
    </div>
</x-layouts.admin>