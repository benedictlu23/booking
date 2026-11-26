<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Agro-Inland Resort — Book your perfect nature getaway' }}">

    <title>{{ $title ?? config('app.name') }} | Agro-Inland Resort</title>

    <tallstackui:script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-[#F7F5EF] text-[#292D29] font-sans selection:bg-[#233D32] selection:text-[#F7F5EF]">

    {{-- Navigation --}}
    <nav x-data="{ mobileOpen: false, scrolled: false }"
         @scroll.window="scrolled = window.scrollY > 20"
         :class="scrolled ? 'bg-[#F7F5EF]/95 backdrop-blur-md border-b border-[#233D32]/10 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.08)]' : 'bg-[#F7F5EF]/80 backdrop-blur-sm border-b border-[#233D32]/5'"
         class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                {{-- Logo & Brand Identity --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 group">
                    <div class="w-11 h-11 rounded-xl bg-white/80 border border-[#233D32]/10 p-1 flex items-center justify-center shadow-xs group-hover:border-[#A68B61]/50 transition-all">
                        <img src="https://hhrl.lyr.com.ph/assets/images/1772440510.png" alt="Agro-Inland Resort Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-serif font-bold text-lg text-[#233D32] leading-tight tracking-tight">AIR</span>
                        <span class="text-[10px] tracking-[0.18em] uppercase font-semibold text-[#A68B61]">Agro Inland Resort</span>
                    </div>
                </a>

                {{-- Desktop Navigation Links --}}
                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors {{ request()->routeIs('home') ? 'text-[#233D32] border-b-2 border-[#A68B61] pb-1' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('rooms.search') }}"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors {{ request()->routeIs('rooms.search') ? 'text-[#233D32] border-b-2 border-[#A68B61] pb-1' : '' }}">
                        Rooms
                    </a>
                    <a href="{{ route('cottages.search') }}"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors {{ request()->routeIs('cottages.search') ? 'text-[#233D32] border-b-2 border-[#A68B61] pb-1' : '' }}">
                        Cottages
                    </a>
                    <a href="{{ route('home') }}#amenities"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors">
                        Amenities
                    </a>
                    <a href="{{ route('home') }}#location"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors">
                        Location
                    </a>
                    <a href="{{ route('home') }}#location"
                       class="text-xs font-semibold uppercase tracking-wider text-[#292D29]/80 hover:text-[#233D32] transition-colors">
                        Contact
                    </a>
                </div>

                {{-- Auth Actions --}}
                <div class="hidden lg:flex items-center gap-3.5">
                    @auth
                        @if(auth()->user()->isAdmin() || auth()->user()->isDeskStaff())
                            <a href="{{ route('admin.dashboard') }}"
                               class="text-xs font-semibold uppercase tracking-wider px-3.5 py-2 bg-[#EFEBE1] text-[#233D32] rounded-lg border border-[#233D32]/15 hover:bg-[#233D32] hover:text-[#F7F5EF] transition-all">
                                Admin Panel
                            </a>
                        @endif
                        @if(auth()->user()->isHousekeeper())
                            <a href="{{ route('housekeeper.dashboard') }}"
                               class="text-xs font-semibold uppercase tracking-wider px-3.5 py-2 bg-[#78866B]/15 text-[#233D32] rounded-lg border border-[#78866B]/30 hover:bg-[#78866B] hover:text-white transition-all flex items-center gap-1.5">
                                <span>🧹</span> Housekeeper
                            </a>
                        @endif
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-[#EFEBE1] border border-transparent hover:border-[#233D32]/10 transition-all">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                                         class="w-8 h-8 rounded-full object-cover ring-2 ring-[#A68B61]">
                                @else
                                    <div class="w-8 h-8 bg-[#233D32] text-[#F7F5EF] rounded-full flex items-center justify-center font-bold text-xs">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-xs font-semibold text-[#292D29] max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-3.5 h-3.5 text-[#777A72]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-[0_10px_35px_-5px_rgba(35,61,50,0.12)] border border-[#233D32]/10 py-2 z-50">
                                <div class="px-4 py-3 border-b border-[#233D32]/10 bg-[#FAF8F4]">
                                    <p class="text-sm font-semibold text-[#292D29] leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-[#777A72] mt-0.5 truncate">{{ auth()->user()->email }}</p>
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#EFEBE1] text-[#233D32] border border-[#233D32]/10">
                                            {{ auth()->user()->visit_count }} visits
                                        </span>
                                        <span class="text-[10px] uppercase font-bold text-[#A68B61] tracking-wider">{{ auth()->user()->role }}</span>
                                    </div>
                                </div>
                                @if(auth()->user()->isHousekeeper())
                                    <a href="{{ route('housekeeper.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-[#292D29] hover:bg-[#FAF8F4] transition-colors">
                                        🧹 Housekeeper Tasks
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-[#292D29] hover:bg-[#FAF8F4] transition-colors">
                                    <svg class="w-4 h-4 text-[#777A72]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    My Dashboard
                                </a>
                                <a href="{{ route('customer.bookings') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-[#292D29] hover:bg-[#FAF8F4] transition-colors">
                                    <svg class="w-4 h-4 text-[#777A72]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    My Bookings
                                </a>
                                <div class="border-t border-[#233D32]/10 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition-colors">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold uppercase tracking-wider text-[#292D29] hover:text-[#233D32] px-3 py-2 transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('rooms.search') }}"
                           class="inline-flex items-center justify-center text-xs font-semibold uppercase tracking-widest px-5 py-2.5 bg-[#233D32] text-[#F7F5EF] rounded-xl hover:bg-[#172A22] transition-all shadow-[0_2px_10px_rgba(35,61,50,0.15)] border border-[#233D32]">
                            Book Now
                        </a>
                    @endauth
                </div>

                {{-- Mobile menu button --}}
                <button @click="mobileOpen = !mobileOpen"
                        aria-label="Toggle Navigation Menu"
                        class="lg:hidden p-2 rounded-xl text-[#233D32] hover:bg-[#EFEBE1] border border-[#233D32]/10 transition-colors">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Drawer Menu --}}
        <div x-show="mobileOpen" x-transition
             class="lg:hidden border-t border-[#233D32]/10 bg-[#F7F5EF] shadow-xl">
            <div class="px-5 py-5 space-y-2">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Home</a>
                <a href="{{ route('rooms.search') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Rooms</a>
                <a href="{{ route('cottages.search') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Cottages</a>
                <a href="{{ route('home') }}#amenities" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Amenities</a>
                <a href="{{ route('home') }}#location" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Location</a>
                @auth
                    <div class="pt-3 border-t border-[#233D32]/10 space-y-2">
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#233D32] rounded-lg hover:bg-[#EFEBE1]">My Dashboard</a>
                        <a href="{{ route('customer.bookings') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#233D32] rounded-lg hover:bg-[#EFEBE1]">My Bookings</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isDeskStaff())
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#A68B61] rounded-lg hover:bg-[#EFEBE1]">Admin Panel</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2.5 text-sm font-semibold text-rose-700 rounded-lg hover:bg-rose-50">Sign Out</button>
                        </form>
                    </div>
                @else
                    <div class="pt-3 border-t border-[#233D32]/10 space-y-2">
                        <a href="{{ route('login') }}" class="block px-3 py-2.5 text-sm font-semibold text-[#292D29] rounded-lg hover:bg-[#EFEBE1]">Sign In</a>
                        <a href="{{ route('rooms.search') }}" class="block w-full text-center px-4 py-3 text-xs font-semibold uppercase tracking-widest bg-[#233D32] text-[#F7F5EF] rounded-xl shadow-xs">Book Now</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 x-transition
                 class="fixed top-24 right-4 z-50 max-w-sm bg-[#233D32] text-[#F7F5EF] px-5 py-4 rounded-xl shadow-xl border border-[#A68B61]/40 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#A68B61]/30 flex items-center justify-center shrink-0 text-[#FAF8F4]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-xs font-medium leading-relaxed">{{ session('success') }}</p>
                <button @click="show = false" class="ml-auto shrink-0 text-[#FAF8F4]/70 hover:text-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Redesigned Countryside Luxury Footer --}}
    <footer class="bg-[#172A22] text-[#EFEBE1] border-t border-[#233D32]/40 mt-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">

                {{-- Brand Column --}}
                <div class="md:col-span-5">
                    <div class="flex items-center gap-3.5 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-white/10 p-1.5 flex items-center justify-center border border-white/15">
                            <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Agro-Inland Logo" class="w-full h-full object-contain brightness-110">
                        </div>
                        <div>
                            <p class="font-serif font-bold text-xl text-[#F7F5EF] leading-tight">Agro-Inland Resort</p>
                            <p class="text-[10px] tracking-[0.2em] uppercase font-semibold text-[#A68B61]">Countryside Hospitality & Nature Retreat</p>
                        </div>
                    </div>
                    <p class="text-xs text-[#A8B29A] leading-relaxed max-w-sm mt-3">
                        A peaceful sanctuary rooted in nature. Discover restful accommodations, farm-fresh air, authentic countryside tranquility, and attentive Filipino hospitality.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div class="md:col-span-3">
                    <h4 class="text-xs uppercase tracking-[0.18em] font-semibold text-[#A68B61] mb-4">Explore</h4>
                    <ul class="space-y-2.5 text-xs text-[#EFEBE1]/80">
                        <li><a href="{{ route('rooms.search') }}" class="hover:text-white transition-colors">Rooms & Cottages</a></li>
                        <li><a href="{{ route('home') }}#amenities" class="hover:text-white transition-colors">Resort Amenities</a></li>
                        <li><a href="{{ route('home') }}#location" class="hover:text-white transition-colors">Location & Directions</a></li>
                    </ul>
                </div>

                {{-- Account & Portals --}}
                <div class="md:col-span-4">
                    <h4 class="text-xs uppercase tracking-[0.18em] font-semibold text-[#A68B61] mb-4">Guest & Staff Portals</h4>
                    <ul class="space-y-2.5 text-xs text-[#EFEBE1]/80">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">My Guest Dashboard</a></li>
                            <li><a href="{{ route('customer.bookings') }}" class="hover:text-white transition-colors">My Bookings</a></li>
                            @if(auth()->user()->isAdmin() || auth()->user()->isDeskStaff())
                                <li><a href="{{ route('admin.dashboard') }}" class="text-[#A68B61] hover:text-white transition-colors font-medium">Administration Panel</a></li>
                            @endif
                            @if(auth()->user()->isHousekeeper())
                                <li><a href="{{ route('housekeeper.dashboard') }}" class="text-[#A68B61] hover:text-white transition-colors font-medium">Housekeeper Portal</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In to Your Account</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register as a Guest</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="border-t border-[#233D32] mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#78866B]">
                <p>&copy; {{ date('Y') }} Agro-Inland Resort. All rights reserved.</p>
                <p class="font-serif italic text-xs text-[#A8B29A]">Contemporary Countryside Luxury</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    <script>
        // Alpine.js is auto-injected by Livewire
    </script>
</body>
</html>
