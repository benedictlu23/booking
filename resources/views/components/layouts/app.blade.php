<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Agro-Inland Resort — Book your perfect nature getaway' }}">

    <title>{{ $title ?? config('app.name') }} | Agro-Inland Resort</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-stone-50 text-stone-900">

    {{-- Navigation --}}
    <nav x-data="{ mobileOpen: false, scrolled: false }"
         @scroll.window="scrolled = window.scrollY > 20"
         :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-transparent'"
         class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="AIR" class="w-12 h-12">
                    </div>
                    <div>
                        <p class="font-bold text-stone-900 leading-none text-sm">Agro-Inland</p>
                        <p class="text-xs text-emerald-600 font-medium">Resort & Nature Retreat</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Home</a>
                    <a href="{{ route('rooms.search') }}" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Rooms & Cottages</a>
                    <a href="{{ route('home') }}#amenities" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Amenities</a>
                    <a href="{{ route('home') }}#location" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Location</a>
                    <a href="{{ route('home') }}#contact" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Contact</a>
                </div>

                {{-- Auth Actions --}}
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isAdmin() || auth()->user()->isDeskStaff())
                            <a href="{{ route('admin.dashboard') }}"
                               class="text-sm font-medium px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                                Admin Panel
                            </a>
                        @endif
                        @if(auth()->user()->isHousekeeper())
                            <a href="{{ route('housekeeper.dashboard') }}"
                               class="text-sm font-medium px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
                                <span>🧹</span> Housekeeper Portal
                            </a>
                        @endif
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-full hover:bg-stone-100 transition-colors">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                                         class="w-9 h-9 rounded-full object-cover ring-2 ring-emerald-500">
                                @else
                                    <div class="w-9 h-9 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <svg class="w-4 h-4 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                  class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-stone-100">
                                    <p class="text-sm font-semibold text-stone-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-stone-500">{{ auth()->user()->email }}</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="text-xs text-emerald-600 font-medium">{{ auth()->user()->visit_count }} visits</span>
                                    </div>
                                </div>
                                @if(auth()->user()->isHousekeeper())
                                    <a href="{{ route('housekeeper.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                                        🧹 Housekeeper Tasks
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                                    My Dashboard
                                </a>
                                <a href="{{ route('customer.bookings') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                                    My Bookings
                                </a>
                                <div class="border-t border-stone-100 mt-2 pt-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-stone-700 hover:text-emerald-600 transition-colors">Sign In</a>
                        <a href="{{ route('rooms.search') }}"
                           class="text-sm font-semibold px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                            Book Now
                        </a>
                    @endauth
                </div>

                {{-- Mobile menu button --}}
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg hover:bg-stone-100">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-transition class="lg:hidden border-t border-stone-100 bg-white/95 backdrop-blur-md">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-sm font-medium text-stone-700 rounded-lg hover:bg-stone-50">Home</a>
                <a href="{{ route('rooms.search') }}" class="block px-3 py-2 text-sm font-medium text-stone-700 rounded-lg hover:bg-stone-50">Rooms & Cottages</a>
                <a href="{{ route('home') }}#location" class="block px-3 py-2 text-sm font-medium text-stone-700 rounded-lg hover:bg-stone-50">Location</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-sm font-medium text-stone-700 rounded-lg hover:bg-stone-50">My Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-medium text-stone-700 rounded-lg hover:bg-stone-50">Sign In</a>
                    <a href="{{ route('rooms.search') }}" class="block w-full text-center px-3 py-2.5 text-sm font-semibold bg-emerald-600 text-white rounded-xl">Book Now</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 x-transition class="fixed top-20 right-4 z-50 max-w-sm bg-emerald-600 text-white px-6 py-4 rounded-2xl shadow-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto shrink-0 opacity-80 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-900 text-stone-300 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                            </svg>
                        </div>
                        <p class="font-bold text-white text-lg">Agro-Inland Resort</p>
                    </div>
                    <p class="text-sm leading-relaxed max-w-xs">Your perfect escape into nature. Experience the beauty of farm life combined with modern comfort and amenities.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('rooms.search') }}" class="hover:text-emerald-400 transition-colors">Browse Rooms</a></li>
                        <li><a href="{{ route('home') }}#amenities" class="hover:text-emerald-400 transition-colors">Amenities</a></li>
                        <li><a href="{{ route('home') }}#location" class="hover:text-emerald-400 transition-colors">Location</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3">Account</h4>
                    <ul class="space-y-2 text-sm">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="hover:text-emerald-400 transition-colors">My Dashboard</a></li>
                            <li><a href="{{ route('customer.bookings') }}" class="hover:text-emerald-400 transition-colors">My Bookings</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Sign In</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-emerald-400 transition-colors">Register</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
            <div class="border-t border-stone-800 mt-12 pt-6 text-sm text-stone-500 text-center">
                &copy; {{ date('Y') }} Agro-Inland Resort. All rights reserved.
            </div>
        </div>
    </footer>

    @livewireScripts
    <script>
        // Alpine.js is auto-injected by Livewire
    </script>
</body>
</html>
