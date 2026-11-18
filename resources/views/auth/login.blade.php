<x-layouts.app>
    <x-slot:title>Guest Sign In | Agro-Inland Resort</x-slot:title>

    <div class="min-h-screen flex items-center justify-center bg-[#F7F5EF] px-4 pt-28 pb-16">
        <div class="w-full max-w-md">
            {{-- Sign In Folio Card --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(35,61,50,0.08)] p-8 sm:p-10 border border-[#233D32]/10">
                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 p-2 flex items-center justify-center mx-auto mb-4 shadow-xs">
                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Agro-Inland Resort" class="w-full h-full object-contain">
                    </div>
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Guest & Member Portal</span>
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#233D32] mt-1">Welcome Back</h1>
                    <p class="text-xs text-[#777A72] mt-1.5 font-light">Access your reservations, history, and special offers</p>
                </div>

                {{-- Google OAuth Button --}}
                <a href="{{ route('auth.google') }}"
                   class="flex items-center justify-center gap-3 w-full px-4 py-3.5 border border-[#233D32]/15 rounded-xl hover:bg-[#FAF8F4] transition-all font-medium text-xs uppercase tracking-wider text-[#292D29] mb-6 shadow-2xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-[#233D32]/10"></div>
                    </div>
                    <div class="relative flex justify-center text-[11px]">
                        <span class="bg-white px-3 text-[#777A72] uppercase tracking-wider font-medium">or continue with email</span>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="w-full px-4 py-3 rounded-xl border border-[#233D32]/15 bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] text-sm font-medium text-[#292D29] transition-all @error('email') border-rose-400 bg-rose-50/50 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="w-full px-4 py-3 rounded-xl border border-[#233D32]/15 bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] text-sm font-medium text-[#292D29] transition-all @error('password') border-rose-400 bg-rose-50/50 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-[#233D32]/20 text-[#233D32] focus:ring-[#233D32]">
                            <span class="text-xs text-[#595D58] font-medium">Keep me signed in</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold text-xs uppercase tracking-widest rounded-xl transition-all shadow-[0_4px_15px_rgba(35,61,50,0.18)] hover:-translate-y-0.5 mt-2">
                        Sign In
                    </button>
                </form>

                <p class="text-center text-xs text-[#777A72] mt-6 font-light">
                    Don't have an account yet?
                    <a href="{{ route('register') }}" class="text-[#233D32] font-semibold underline underline-offset-2 hover:text-[#A68B61] transition-colors">Create account</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
