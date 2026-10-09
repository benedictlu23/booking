<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} | Agro-Inland Resort</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-stone-100" x-data="adminPanel()">

    {{-- Real-time notification toast --}}
    <div id="booking-toast" class="fixed top-4 right-4 z-[9999] space-y-2" x-data="bookingNotifier()">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification.visible" x-transition
                 class="max-w-sm bg-white border border-emerald-200 rounded-2xl shadow-xl p-4 flex items-start gap-3">
                <div class="shrink-0 w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-stone-900 text-sm" x-text="notification.title"></p>
                    <p class="text-xs text-stone-600 mt-0.5" x-text="notification.message"></p>
                </div>
                <button @click="notification.visible = false" class="shrink-0 text-stone-400 hover:text-stone-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        <aside class="w-64 bg-stone-900 text-white flex flex-col shrink-0">
            {{-- Logo --}}
            <div class="p-6 border-b border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Agro-Inland</p>
                        <p class="text-xs text-stone-400">Admin Panel</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-4 overflow-y-auto">
                <div class="space-y-1">
                    @php
                        $navItems = [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                            ['route' => 'admin.bookings.matrix', 'label' => 'Reservation Matrix', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
                            ['route' => 'admin.bookings.index', 'label' => 'Bookings', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['route' => 'admin.rooms.index', 'label' => 'Rooms', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                            ['route' => 'admin.discounts.index', 'label' => 'Discounts', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                            ['route' => 'admin.carousels.index', 'label' => 'CMS / Carousel', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['route' => 'admin.settings.index', 'label' => 'Resort Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                                  {{ request()->routeIs($item['route']) ? 'bg-emerald-600 text-white' : 'text-stone-400 hover:text-white hover:bg-stone-800' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </nav>

            {{-- User info --}}
            <div class="p-4 border-t border-stone-800">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover">
                    @else
                        <div class="w-9 h-9 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-stone-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out" class="text-stone-500 hover:text-stone-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-10 bg-white border-b border-stone-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-stone-900">{{ $heading ?? 'Admin Panel' }}</h1>
                        @isset($subheading)
                            <p class="text-sm text-stone-500 mt-0.5">{{ $subheading }}</p>
                        @endisset
                    </div>
                    <div class="flex items-center gap-3">
                        @if(session('success'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 class="px-4 py-2 bg-emerald-100 text-emerald-800 rounded-lg text-sm font-medium">
                                {{ session('success') }}
                            </div>
                        @endif
                        <a href="{{ route('home') }}" target="_blank"
                           class="text-sm text-stone-500 hover:text-stone-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            View Site
                        </a>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="p-6">
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
                if (typeof window.Echo !== 'undefined') {
                    window.Echo.channel('bookings')
                        .listen('.NewBookingReceived', (e) => {
                            this.addNotification(
                                '🎉 New Booking!',
                                `${e.guest_name} booked Room ${e.room_number} — ${e.booking_code}`
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
