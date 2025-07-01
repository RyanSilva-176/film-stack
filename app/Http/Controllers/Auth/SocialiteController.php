<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function create($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function store($provider)
    {
        $socialiteUser = Socialite::driver($provider)->user();

        Log::info('Socialite user data:', [
            'id' => $socialiteUser->getId(),
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
        ]);

        $user = User::where('email', $socialiteUser->getEmail())->first();

        if (!$user) {
            Log::warning('User not found, creating a new user:', [
                'email' => $socialiteUser->getEmail(),
            ]);

            $user = User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        $user->update([
            'socialite_id' => $socialiteUser->getId(),
            'socialite_token' => $socialiteUser->token,
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
