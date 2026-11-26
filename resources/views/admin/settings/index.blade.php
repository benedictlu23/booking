<x-layouts.admin>
    <x-slot:heading>Resort Settings</x-slot:heading>
    <x-slot:subheading>Configure resort operational details, contact channels, and policies</x-slot:subheading>

    <div class="flex items-center gap-2 mb-6 border-b border-stone-200/80 pb-4">
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-[#F7F5EF] bg-[#233D32] shadow-xs">
            General Settings
        </a>
        <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
            Room Categories
        </a>
        <a href="{{ route('admin.amenities.index') }}" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
            Resort Amenities
        </a>
        <a href="{{ route('admin.payment-methods.index') }}" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl text-stone-600 bg-white border border-stone-200 hover:bg-[#F7F5EF] hover:text-[#233D32] transition-colors">
            Payment Methods
        </a>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            @forelse($settings as $group => $groupSettings)
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-stone-200/80">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-stone-100">
                        <div class="w-2 h-2 rounded-full bg-[#A68B61]"></div>
                        <h3 class="font-serif text-lg font-bold text-[#172A22] capitalize">{{ str_replace('_', ' ', $group) }}</h3>
                    </div>

                    <div class="space-y-4">
                        @foreach($groupSettings as $setting)
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2 capitalize">
                                    {{ str_replace('_', ' ', $setting->key) }}
                                </label>
                                @if(in_array($setting->key, ['pet_policy', 'resort_description']))
                                    <textarea name="settings[{{ $setting->key }}]" rows="3"
                                              class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none">{{ $setting->value }}</textarea>
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-12 text-center shadow-xs border border-stone-200/80">
                    <p class="font-serif text-stone-600 text-base">No resort operational settings found.</p>
                    <p class="text-xs text-stone-400 mt-2 font-mono">Run php artisan db:seed to initialize default parameters.</p>
                </div>
            @endforelse

            @if($settings->isNotEmpty())
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-medium rounded-xl text-xs uppercase tracking-wider transition-colors shadow-xs">
                        Save Resort Settings
                    </button>
                </div>
            @endif
        </form>
    </div>
</x-layouts.admin>
