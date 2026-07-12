<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class SocialiteController extends Controller
{
    /**
     * Placeholder until Laravel Socialite + provider apps are configured.
     * To enable: composer require laravel/socialite, add provider keys to
     * config/services.php, then implement redirect()/callback() per provider.
     */
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook', 'apple'], true), 404);

        return redirect()->route('login')
            ->with('status', 'تسجيل الدخول عبر '.$provider.' سيتوفر قريباً.');
    }
}
