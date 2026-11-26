@php
    try {
        $recentBookingsSeed = \App\Models\Booking::with('room')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'guest_name' => $b->guest_name,
                    'room_number' => $b->room?->room_number ?? 'Suite',
                    'total_price' => (float) $b->total_price,
                    'status' => $b->status,
                    'created_at' => $b->created_at?->diffForHumans() ?? 'Recently',
                    'timestamp' => $b->created_at?->timestamp * 1000 ?? now()->timestamp * 1000,
                    'type' => 'booking',
                    'title' => 'Reservation Folio',
                    'message' => "{$b->guest_name} reserved " . ($b->room?->room_number ? 'Room ' . $b->room->room_number : 'Room') . " — {$b->booking_code}",
                    'read' => true,
                ];
            });
    } catch (\Throwable) {
        $recentBookingsSeed = collect();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Resort Operations Console' }} | Agro-Inland Resort</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-[#FAF8F4] text-[#292D29] h-full overflow-hidden" x-data="bookingNotifier()">

    {{-- Real-time notification toast --}}
    <div id="booking-toast" class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto max-w-sm bg-white/95 backdrop-blur-md border border-[#233D32]/15 rounded-2xl shadow-[0_12px_40px_rgba(23,42,34,0.15)] p-4 flex items-start gap-3.5">
                <div class="shrink-0 w-9 h-9 bg-[#FAF8F4] border border-[#233D32]/10 rounded-xl flex items-center justify-center text-[#233D32]">
                    <svg class="w-4 h-4 text-[#A68B61]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-serif font-bold text-[#233D32] text-sm" x-text="toast.title"></p>
                    <p class="text-xs text-[#777A72] mt-0.5 font-light leading-relaxed" x-text="toast.message"></p>
                </div>
                <button @click="toast.visible = false" class="shrink-0 text-[#777A72] hover:text-[#233D32] p-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <div class="flex h-screen overflow-hidden">
        {{-- Luxury Dark Evergreen Sidebar --}}
        <aside class="w-64 bg-[#172A22] text-[#F7F5EF] flex flex-col shrink-0 border-r border-[#233D32]/30 shadow-[4px_0_24px_rgba(23,42,34,0.1)]">
            {{-- Resort Identity --}}
            <div class="p-6 border-b border-[#233D32]/40">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-[#233D32] border border-[#A68B61]/30 p-1.5 flex items-center justify-center shrink-0 shadow-xs">
                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Agro-Inland Resort" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <p class="font-serif font-bold text-sm tracking-wide text-[#F7F5EF] truncate">Agro-Inland Resort</p>
                        <p class="text-[10px] uppercase tracking-[0.18em] text-[#A68B61] font-semibold mt-0.5">Management Console</p>
                    </div>
                </div>
            </div>

            {{-- Operational Navigation --}}
            <nav class="flex-1 p-3.5 overflow-y-auto space-y-1 no-scrollbar">
                <div class="px-3 pt-2 pb-1.5 text-[9px] uppercase tracking-[0.24em] font-bold text-[#A8B29A]/70">
                    Operations & Front Desk
                </div>

                @php
                    $navItems = [
                        ['route' => 'admin.dashboard', 'label' => 'Executive Overview', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'admin.bookings.matrix', 'label' => 'Reservation Matrix', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
                        ['route' => 'admin.bookings.index', 'label' => 'Folio Bookings', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'admin.rooms.index', 'label' => 'Room Inventory', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['route' => 'admin.housekeeping.index', 'label' => 'Housekeeping Log', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ['route' => 'admin.customers.index', 'label' => 'Guest Directory', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ['route' => 'admin.discounts.index', 'label' => 'Promotions & Rates', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        ['route' => 'admin.carousels.index', 'label' => 'Resort CMS / Media', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'admin.payment-methods.index', 'label' => 'Payment Methods', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['route' => 'admin.settings.index', 'label' => 'Resort Configuration', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || (str_contains($item['route'], 'admin.rooms') && request()->routeIs('admin.rooms.*')) || (str_contains($item['route'], 'admin.payment-methods') && request()->routeIs('admin.payment-methods.*'));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all
                              {{ $isActive ? 'bg-[#233D32] text-[#F7F5EF] border border-[#A68B61]/40 shadow-xs' : 'text-[#A8B29A] hover:text-[#F7F5EF] hover:bg-[#233D32]/40' }}">
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-[#A68B61]' : 'text-[#78866B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Administrator Profile & Sign Out --}}
            <div class="p-4 border-t border-[#233D32]/40 bg-[#14231c]">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-9 h-9 rounded-xl object-cover border border-[#A68B61]/40 shadow-xs">
                    @else
                        <div class="w-9 h-9 bg-[#233D32] border border-[#A68B61]/30 rounded-xl flex items-center justify-center text-[#F7F5EF] font-serif font-bold text-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-serif font-semibold text-[#F7F5EF] truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-block text-[9px] uppercase tracking-wider text-[#A68B61] font-semibold">Resort {{ auth()->user()->role }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out" class="text-[#A8B29A] hover:text-[#F7F5EF] p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Workspace Canvas --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#FAF8F4]">
            {{-- Executive Top Bar --}}
            <header class="bg-white/80 backdrop-blur-md border-b border-[#233D32]/10 px-6 sm:px-8 py-4 shrink-0 shadow-2xs">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-lg sm:text-xl font-serif font-bold text-[#233D32]">{{ $heading ?? 'Resort Operations' }}</h1>
                        @isset($subheading)
                            <p class="text-xs text-[#777A72] font-light mt-0.5">{{ $subheading }}</p>
                        @endisset
                    </div>
                    <div class="flex items-center gap-3">
                        @if(session('success'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 class="px-4 py-1.5 bg-[#FAF8F4] border border-[#233D32]/15 text-[#233D32] rounded-xl text-xs font-semibold shadow-2xs">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Real-Time Booking Notification Center Dropdown --}}
                        <div class="relative" x-data="{ menuOpen: false }" @click.outside="menuOpen = false" @keydown.escape.window="menuOpen = false">
                            {{-- Bell Icon Button --}}
                            <button @click="menuOpen = !menuOpen; if(menuOpen && unreadCount > 0) markAllAsRead()"
                                    type="button"
                                    class="relative inline-flex items-center justify-center p-2 rounded-xl border border-[#233D32]/15 hover:border-[#233D32]/30 bg-white text-[#233D32] hover:bg-[#FAF8F4] transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#233D32]/20"
                                    :class="{ 'border-[#A68B61]/60 bg-[#FAF8F4] ring-2 ring-[#A68B61]/20': menuOpen }"
                                    aria-label="Real-time Booking Notifications"
                                    title="Real-time Booking Notifications (Reverb)">
                                
                                {{-- Bell SVG with gentle bounce when new booking arrives --}}
                                <svg class="w-4 h-4 text-[#233D32] transition-transform"
                                     :class="{ 'animate-bounce text-[#A68B61]': isRinging }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>

                                {{-- Pulse Indicator & Unread Count Badge --}}
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex items-center justify-center px-1 h-4 min-w-4 rounded-full bg-rose-600 text-white text-[9px] font-bold leading-none shadow-xs"
                                              x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                                    </span>
                                </template>
                            </button>

                            {{-- Dropdown Notification Panel --}}
                            <div x-show="menuOpen"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2.5 w-80 sm:w-96 bg-white/95 backdrop-blur-md border border-[#233D32]/15 rounded-2xl shadow-[0_16px_40px_rgba(23,42,34,0.18)] z-50 overflow-hidden"
                                 style="display: none;">
                                
                                {{-- Dropdown Header --}}
                                <div class="px-4 py-3 bg-[#FAF8F4] border-b border-[#233D32]/10 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="font-serif font-bold text-sm text-[#233D32]">Booking Alerts</span>
                                        {{-- Reverb Live Status Indicator --}}
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium"
                                              :class="reverbConnected ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-amber-50 text-amber-800 border border-amber-200/80'">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                  :class="reverbConnected ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                                            <span x-text="reverbConnected ? 'Reverb Live' : 'Reverb Syncing'"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        {{-- Sound Chime Toggle --}}
                                        <button type="button" @click="toggleSound()"
                                                :title="soundEnabled ? 'Chime sound: Enabled (Click to mute)' : 'Chime sound: Muted (Click to enable)'"
                                                class="p-1.5 rounded-lg text-stone-500 hover:text-[#233D32] hover:bg-stone-100 transition-colors"
                                                aria-label="Toggle chime sound">
                                            <template x-if="soundEnabled">
                                                <svg class="w-3.5 h-3.5 text-[#233D32]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                                </svg>
                                            </template>
                                            <template x-if="!soundEnabled">
                                                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                                                </svg>
                                            </template>
                                        </button>

                                        {{-- Clear list button --}}
                                        <template x-if="notifications.length > 0">
                                            <button type="button" @click="clearNotifications()"
                                                    class="text-[11px] text-stone-500 hover:text-rose-600 px-1.5 py-0.5 rounded transition-colors"
                                                    title="Clear notification list">
                                                Clear
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Optional Desktop Notification Permission Request Banner --}}
                                <template x-if="desktopPermissionNeeded">
                                    <div class="px-4 py-2 bg-[#EFEBE1]/80 border-b border-[#233D32]/10 flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-[#233D32]">Enable desktop alerts for incoming bookings?</span>
                                        <button type="button" @click="requestDesktopPermission()"
                                                class="px-2 py-0.5 bg-[#233D32] text-white text-[10px] font-semibold rounded-md hover:bg-[#172A22] transition-colors">
                                            Enable
                                        </button>
                                    </div>
                                </template>

                                {{-- Notifications Scrollable Body --}}
                                <div class="max-h-80 sm:max-h-96 overflow-y-auto divide-y divide-stone-100">
                                    <template x-if="notifications.length === 0">
                                        <div class="p-8 text-center">
                                            <div class="w-10 h-10 rounded-full bg-[#FAF8F4] border border-[#233D32]/10 mx-auto flex items-center justify-center text-[#A68B61] mb-2.5">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                </svg>
                                            </div>
                                            <p class="font-serif font-bold text-sm text-[#233D32]">All caught up!</p>
                                            <p class="text-xs text-stone-500 mt-1 font-light leading-relaxed">
                                                Listening in real-time via Reverb. New guest reservations will appear here immediately.
                                            </p>
                                        </div>
                                    </template>

                                    <template x-for="(item, index) in notifications" :key="item.timestamp + '-' + index">
                                        <a :href="item.id ? ('/admin/bookings/' + item.id) : ('{{ route('admin.bookings.index') }}')"
                                           @click="item.read = true; saveToStorage()"
                                           class="p-3.5 flex items-start gap-3 hover:bg-[#FAF8F4]/80 transition-colors group block relative"
                                           :class="{ 'bg-[#FAF8F4]/40': !item.read }">
                                            
                                            {{-- Unread Dot --}}
                                            <template x-if="!item.read">
                                                <span class="absolute left-1.5 top-5 w-1.5 h-1.5 rounded-full bg-[#A68B61]"></span>
                                            </template>

                                            {{-- Icon by Notification Type --}}
                                            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center shadow-2xs mt-0.5"
                                                 :class="{
                                                    'bg-emerald-50 text-emerald-700 border border-emerald-200/80': item.type === 'booking',
                                                    'bg-rose-50 text-rose-700 border border-rose-200/80': item.type === 'cancelled',
                                                    'bg-sky-50 text-sky-700 border border-sky-200/80': item.type === 'checkin'
                                                 }">
                                                <template x-if="item.type === 'booking'">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </template>
                                                <template x-if="item.type === 'cancelled'">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </template>
                                                <template x-if="item.type === 'checkin'">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </template>
                                            </div>

                                            {{-- Content Info --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-1">
                                                    <p class="text-xs font-semibold text-[#233D32] truncate" x-text="item.guest_name || 'Guest'"></p>
                                                    <span class="text-[10px] text-stone-400 shrink-0" x-text="item.created_at"></span>
                                                </div>
                                                <p class="text-xs text-stone-600 font-light mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                                
                                                <div class="flex items-center gap-2 mt-1.5 text-[10px]">
                                                    <span class="font-mono px-1.5 py-0.5 bg-stone-100 rounded text-stone-700" x-text="item.booking_code"></span>
                                                    <template x-if="item.total_price">
                                                        <span class="font-semibold text-[#A68B61]" x-text="'₱' + Number(item.total_price).toLocaleString()"></span>
                                                    </template>
                                                    <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded font-semibold ml-auto"
                                                          :class="{
                                                              'bg-amber-100 text-amber-800': item.status === 'pending',
                                                              'bg-emerald-100 text-emerald-800': item.status === 'confirmed' || item.status === 'checked_in',
                                                              'bg-rose-100 text-rose-800': item.status === 'cancelled'
                                                          }"
                                                          x-text="item.status"></span>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>

                                {{-- Dropdown Footer --}}
                                <div class="p-2.5 bg-[#FAF8F4] border-t border-[#233D32]/10 text-center">
                                    <a href="{{ route('admin.bookings.index') }}"
                                       class="text-xs font-semibold text-[#233D32] hover:text-[#172A22] uppercase tracking-wider block py-1 hover:underline">
                                        View All Folio Reservations →
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Live Guest View Button --}}
                        <a href="{{ route('home') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#233D32]/15 hover:border-[#233D32]/30 bg-white text-xs font-medium text-[#292D29] transition-all shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-[#A68B61]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span>Live Guest View</span>
                        </a>
                    </div>
                </div>
            </header>

            {{-- Viewport Content Container --}}
            <main class="flex-1 overflow-y-auto p-6 sm:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
    window._initialBookingSeeds = @json($recentBookingsSeed);

    function bookingNotifier() {
        return {
            notifications: [],
            unreadCount: 0,
            toasts: [],
            soundEnabled: true,
            reverbConnected: false,
            desktopPermissionNeeded: false,
            isRinging: false,

            init() {
                // Desktop notification permission check
                if ('Notification' in window) {
                    this.desktopPermissionNeeded = (Notification.permission === 'default');
                }

                // Sound preference from localStorage
                const savedSound = localStorage.getItem('agro_admin_notif_sound');
                if (savedSound !== null) {
                    this.soundEnabled = (savedSound === 'true');
                }

                // Load cached notifications from localStorage
                const cached = localStorage.getItem('agro_admin_notifications');
                if (cached) {
                    try {
                        this.notifications = JSON.parse(cached);
                    } catch(e) {
                        this.notifications = [];
                    }
                } else {
                    // Seed initial recent records
                    this.notifications = Array.isArray(window._initialBookingSeeds)
                        ? window._initialBookingSeeds.slice(0, 6)
                        : [];
                }

                this.updateUnreadCount();

                // Initialize Echo Reverb listener
                this.setupEcho();
            },

            setupEcho() {
                if (typeof window.Echo === 'undefined') {
                    setTimeout(() => this.setupEcho(), 200);
                    return;
                }

                // Track Reverb connection status
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    const pusher = window.Echo.connector.pusher;
                    this.reverbConnected = (pusher.connection.state === 'connected');
                    pusher.connection.bind('connected', () => {
                        this.reverbConnected = true;
                        console.log('[Reverb] Connected to WebSocket server');
                    });
                    pusher.connection.bind('unavailable', () => {
                        this.reverbConnected = false;
                        console.warn('[Reverb] Connection unavailable');
                    });
                    pusher.connection.bind('disconnected', () => {
                        this.reverbConnected = false;
                        console.warn('[Reverb] Disconnected');
                    });
                    pusher.connection.bind('connecting', () => {
                        this.reverbConnected = false;
                    });
                    pusher.connection.bind('error', (err) => {
                        console.warn('[Reverb] Connection error:', err);
                    });
                } else {
                    this.reverbConnected = true;
                }

                if (!window._bookingChannelSubscribed) {
                    window._bookingChannelSubscribed = true;

                    const channel = window.Echo.channel('bookings');

                    channel.listen('.NewBookingReceived', (e) => {
                        console.log('[Reverb] .NewBookingReceived event:', e);
                        this.handleNewBooking(e);
                    })
                    .listen('NewBookingReceived', (e) => {
                        console.log('[Reverb] NewBookingReceived event:', e);
                        this.handleNewBooking(e);
                    })
                    .listen('.BookingCancelled', (e) => {
                        this.handleBookingCancelled(e);
                    })
                    .listen('BookingCancelled', (e) => {
                        this.handleBookingCancelled(e);
                    })
                    .listen('.CheckInUpdated', (e) => {
                        this.handleCheckInUpdated(e);
                    })
                    .listen('CheckInUpdated', (e) => {
                        this.handleCheckInUpdated(e);
                    });
                }
            },

            handleNewBooking(e) {
                if (!e || (!e.id && !e.booking_code)) return;
                const dedupKey = `booking-${e.id || ''}-${e.booking_code || ''}`;
                if (!window._processedBookingAlerts) {
                    window._processedBookingAlerts = new Set();
                }
                if (window._processedBookingAlerts.has(dedupKey)) {
                    return;
                }
                window._processedBookingAlerts.add(dedupKey);
                setTimeout(() => { window._processedBookingAlerts.delete(dedupKey); }, 15000);
                const guest = e.guest_name || 'Guest';
                const room = e.room_number ? `Room ${e.room_number}` : (e.room_id ? `Room ${e.room_id}` : 'Suite');
                const code = e.booking_code || 'Booking';
                const price = e.total_price ? Number(e.total_price) : 0;

                const item = {
                    id: e.id,
                    booking_code: code,
                    guest_name: guest,
                    room_number: room,
                    total_price: price,
                    status: e.status || 'pending',
                    created_at: 'Just now',
                    timestamp: Date.now(),
                    type: 'booking',
                    title: '🎉 New Booking Received!',
                    message: `${guest} reserved ${room} — ${code}`,
                    read: false
                };

                this.notifications.unshift(item);
                if (this.notifications.length > 50) {
                    this.notifications = this.notifications.slice(0, 50);
                }
                this.unreadCount++;
                this.saveToStorage();

                // Trigger bell bounce visual
                this.triggerRing();

                // Play reception bell chime
                this.playChime();

                // Show floating toast
                this.addToast('🎉 New Booking Received!', `${guest} booked ${room} — ${code}`);

                // Desktop notification if granted
                this.sendDesktopNotification('🎉 New Booking Received!', `${guest} booked ${room} (${code})`, e.id);

                // Dispatch event so matrix and other components can refresh
                window.dispatchEvent(new CustomEvent('booking-received', { detail: e }));
            },

            handleBookingCancelled(e) {
                const code = e.booking_code || 'Booking';
                const item = {
                    id: e.id,
                    booking_code: code,
                    guest_name: 'Reservation Cancelled',
                    room_number: e.room_number || 'Room',
                    status: 'cancelled',
                    created_at: 'Just now',
                    timestamp: Date.now(),
                    type: 'cancelled',
                    title: '❌ Booking Cancelled',
                    message: `Booking ${code} was cancelled`,
                    read: false
                };

                this.notifications.unshift(item);
                this.unreadCount++;
                this.saveToStorage();
                this.addToast('❌ Booking Cancelled', `Booking ${code} was cancelled.`);
                window.dispatchEvent(new CustomEvent('booking-updated', { detail: e }));
            },

            handleCheckInUpdated(e) {
                const code = e.booking_code || 'Booking';
                const item = {
                    id: e.id,
                    booking_code: code,
                    guest_name: 'Guest Check-In',
                    room_number: e.room_number || 'Room',
                    status: e.status,
                    created_at: 'Just now',
                    timestamp: Date.now(),
                    type: 'checkin',
                    title: '✅ Check-In Updated',
                    message: `Booking ${code} status: ${e.status}`,
                    read: false
                };

                this.notifications.unshift(item);
                this.unreadCount++;
                this.saveToStorage();
                this.addToast('✅ Check-In Updated', `Booking ${code} status: ${e.status}`);
                window.dispatchEvent(new CustomEvent('booking-updated', { detail: e }));
            },

            triggerRing() {
                this.isRinging = true;
                setTimeout(() => { this.isRinging = false; }, 1200);
            },

            addToast(title, message) {
                const toast = { title, message, visible: true, id: Date.now() + Math.random() };
                this.toasts.push(toast);
                setTimeout(() => {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== toast.id);
                    }, 300);
                }, 6000);
            },

            updateUnreadCount() {
                this.unreadCount = this.notifications.filter(n => !n.read).length;
            },

            markAllAsRead() {
                this.notifications.forEach(n => { n.read = true; });
                this.unreadCount = 0;
                this.saveToStorage();
            },

            clearNotifications() {
                this.notifications = [];
                this.unreadCount = 0;
                this.saveToStorage();
            },

            saveToStorage() {
                try {
                    localStorage.setItem('agro_admin_notifications', JSON.stringify(this.notifications));
                } catch(e) {}
                this.updateUnreadCount();
            },

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
                localStorage.setItem('agro_admin_notif_sound', this.soundEnabled ? 'true' : 'false');
                if (this.soundEnabled) {
                    this.playChime();
                }
            },

            playChime() {
                if (!this.soundEnabled) return;
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    if (ctx.state === 'suspended') {
                        ctx.resume();
                    }
                    const now = ctx.currentTime;

                    // Note 1: D5 (587.33Hz)
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now);
                    gain1.gain.setValueAtTime(0.25, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.5);

                    // Note 2: A5 (880Hz)
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.1);
                    gain2.gain.setValueAtTime(0.3, now + 0.1);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.1);
                    osc2.stop(now + 0.8);
                } catch(e) {}
            },

            requestDesktopPermission() {
                if ('Notification' in window) {
                    Notification.requestPermission().then((permission) => {
                        this.desktopPermissionNeeded = (permission === 'default');
                        if (permission === 'granted') {
                            new Notification('Agro-Inland Resort', {
                                body: 'Real-time booking notifications enabled!',
                                icon: '/storage/assets/AIR-LOGO.png'
                            });
                        }
                    });
                }
            },

            sendDesktopNotification(title, message, bookingId) {
                if ('Notification' in window && Notification.permission === 'granted') {
                    const notif = new Notification(title, {
                        body: message,
                        icon: '/storage/assets/AIR-LOGO.png',
                        tag: 'booking-' + (bookingId || Date.now())
                    });
                    notif.onclick = () => {
                        window.focus();
                        if (bookingId) {
                            window.location.href = `/admin/bookings/${bookingId}`;
                        }
                    };
                }
            }
        };
    }
    </script>
</body>
</html>
