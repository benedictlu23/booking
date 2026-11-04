<x-layouts.app>
    <x-slot:title>Sign In</x-slot:title>

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stone-50 via-emerald-50 to-teal-50 px-4 pt-20">
        <div class="w-full max-w-md">
            {{-- Card --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/60 p-8 border border-stone-100">
                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 ">
                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="">
                    </div>
                    <h1 class="text-2xl font-bold text-stone-900">Welcome Back</h1>
                    <p class="text-sm text-stone-500 mt-1">Sign in to your Agro-Inland account</p>
                </div>

                {{-- Google OAuth --}}
                <a href="{{ route('auth.google') }}"
                   class="flex items-center justify-center gap-3 w-full px-4 py-3 border-2 border-stone-200 rounded-xl hover:bg-stone-50 hover:border-stone-300 transition-all font-medium text-sm text-stone-700 mb-6">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </a>

                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-stone-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-stone-400">or sign in with email</span>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition-all @error('email') border-red-400 bg-red-50 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition-all @error('password') border-red-400 bg-red-50 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-stone-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all shadow-lg shadow-emerald-200 hover:shadow-emerald-300 hover:-translate-y-0.5 active:translate-y-0">
                        Sign In
                    </button>
                </form>

                <p class="text-center text-sm text-stone-500 mt-6">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-emerald-600 font-semibold hover:text-emerald-700">Create one</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
