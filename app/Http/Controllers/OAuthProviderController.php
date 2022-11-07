<?php

namespace App\Http\Controllers;

use App\Enums\OAuthProviderEnum;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite as Socialite;

class OAuthProviderController extends Controller
{
    public function redirectToOAuth(OAuthProviderEnum $provider)
    {
        // redirect user to "login with OAuth account" page
        return Socialite::driver($provider->value)->redirect();
    }

    public function handleCallback(OAuthProviderEnum $provider)
    {
        try {
            $socialite = Socialite::driver($provider->value)->user();

            $user = User::firstOrCreate([
                'email' => $socialite->getEmail(),
            ], [
                'name' => $socialite->getName()
            ]);

            $user->OAuthProviders()->updateOrCreate([
                'provider' => $provider,
                'provider_id' => $socialite->getId(),
            ]);

            Auth::login($user);

            return redirect(config('app.frontend_url').'/dashboard');
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
