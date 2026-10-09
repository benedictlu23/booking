<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Google authentication failed. Please try again.']);
        }

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id and avatar if needed
            $updates = [];

            if (empty($user->google_id)) {
                $updates['google_id'] = $googleUser->getId();
            }

            if (empty($user->avatar) || $user->avatar !== $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }

            if (! empty($updates)) {
                $user->update($updates);
            }
        } else {
            // Create new customer account via Google
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Str::random(32),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        // Increment visit counter on every Google login
        $user->increment('visit_count');
        $user->update(['last_visited_at' => now()]);

        if ($user->isAdmin() || $user->isDeskStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
