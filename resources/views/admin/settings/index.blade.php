<x-layouts.admin>
    <x-slot:heading>Resort Settings</x-slot:heading>
    <x-slot:subheading>Configure resort information and policies</x-slot:subheading>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
            @csrf

            @forelse($settings as $group => $groupSettings)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                    <h3 class="font-bold text-stone-900 mb-5 capitalize">{{ str_replace('_', ' ', $group) }}</h3>
                    <div class="space-y-4">
                        @foreach($groupSettings as $setting)
                            <div>
                                <label class="block text-sm font-medium text-stone-700 mb-1.5 capitalize">
                                    {{ str_replace('_', ' ', $setting->key) }}
                                </label>
                                @if(in_array($setting->key, ['pet_policy', 'resort_description']))
                                    <textarea name="settings[{{ $setting->key }}]" rows="3"
                                              class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ $setting->value }}</textarea>
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                           class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-stone-100">
                    <p class="text-stone-400">No settings found. Run the seeder to initialize default settings.</p>
                    <p class="text-xs text-stone-300 mt-2 font-mono">php artisan db:seed</p>
                </div>
            @endforelse

            @if($settings->isNotEmpty())
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-emerald-200">
                        Save Settings
                    </button>
                </div>
            @endif
        </form>
    </div>
</x-layouts.admin>
