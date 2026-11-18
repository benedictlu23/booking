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
<body class="antialiased bg-[#FAF8F4] text-[#292D29] h-full overflow-hidden" x-data="adminPanel()">

    {{-- Real-time notification toast --}}
    <div id="booking-toast" class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none" x-data="bookingNotifier()">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification.visible" x-transition:enter="transition ease-out duration-300"
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
                    <p class="font-serif font-bold text-[#233D32] text-sm" x-text="notification.title"></p>
                    <p class="text-xs text-[#777A72] mt-0.5 font-light leading-relaxed" x-text="notification.message"></p>
                </div>
                <button @click="notification.visible = false" class="shrink-0 text-[#777A72] hover:text-[#233D32] p-1 transition-colors">
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
                        ['route' => 'admin.settings.index', 'label' => 'Resort Configuration', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || (str_contains($item['route'], 'admin.rooms') && request()->routeIs('admin.rooms.*'));
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
    function adminPanel() {
        return {};
    }

    function bookingNotifier() {
        return {
            notifications: [],
            init() {
                if (typeof window.Echo !== 'undefined' && !window._bookingChannelSubscribed) {
                    window._bookingChannelSubscribed = true;
                    window.Echo.channel('bookings')
                        .listen('.NewBookingReceived', (e) => {
                            this.addNotification(
                                '🎉 New Booking!',
                                `${e.guest_name} booked Room ${e.room_number ?? e.room_id} — ${e.booking_code}`
                            );
                            // Dispatch event so matrix can refresh
                            window.dispatchEvent(new CustomEvent('booking-received', { detail: e }));
                        })
                        .listen('.BookingCancelled', (e) => {
                            this.addNotification('❌ Booking Cancelled', `Booking ${e.booking_code} was cancelled.`);
                            window.dispatchEvent(new CustomEvent('booking-updated', { detail: e }));
                        })
                        .listen('.CheckInUpdated', (e) => {
                            this.addNotification('✅ Check-In Updated', `Booking ${e.booking_code} status: ${e.status}`);
                            window.dispatchEvent(new CustomEvent('booking-updated', { detail: e }));
                        });
                }
            },
            addNotification(title, message) {
                const notif = { title, message, visible: true };
                this.notifications.push(notif);
                setTimeout(() => { notif.visible = false; }, 6000);
            }
        };
    }
    </script>
</body>
</html>