<?php

namespace EtsvThor\BifrostBridge\Http\Controllers;

use EtsvThor\BifrostBridge\Data\BifrostUserData;
use EtsvThor\BifrostBridge\Events\BifrostLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use EtsvThor\BifrostBridge\BifrostBridge;
use Illuminate\Contracts\Container\BindingResolutionException;

class LoginController
{
    protected function resolveRedirect(string $config, string $defaultPath = '/')
    {
        $route = config($config);
        if (is_null($route)) {
            return redirect()->to($defaultPath);
        }

        return Route::has($route)
            ? redirect()->route($route)
            : redirect()->to($route);
    }

    protected function notify(string $message, string $type = 'success'): bool
    {
        try {
            app('flash')->message($message, $type);
        } catch (BindingResolutionException $e) {
            return false;
        }

        return true;
    }


    public function redirect(Request $request)
    {
        if (config('bifrost.enabled') !== true) {
            if (! App::environment('local')) {
                abort(500, 'Bifrost is not enabled, authentication is not possible');
            }

            // allow login without password for LOCAL environments when bifrost is NOT enabled
            if($request->has('id')) {
                $user = BifrostBridge::getUserClass()::whereKey($request->get('id', 1))->firstOrFail();
            } else {
                $user = BifrostBridge::getUserClass()::firstOrFail();
            }

            // Login user
            Auth::login($user, config('bifrost.remember_user', true));

            $this->notify($user->name . ' has been logged in automatically, as Bifrost is disabled');

            return $this->resolveRedirect('bifrost.redirects.after_login');
        }

        return Socialite::driver('bifrost')->redirect();
    }

    public function callback()
    {
        /** @var BifrostUserData $data */
        $data = Socialite::driver('bifrost')->user();

        // See if the user exists
        $user = BifrostBridge::resolveAndUpdateUser($data);

        // If we have no user, something was wrong, either no verified email or deleted account
        if (is_null($user)) {
            abort(403, 'Cannot automatically link your account. Contact system administrator.');
        }

        // Login user
        Auth::login($user, config('bifrost.remember_user', true));
        BifrostLogin::dispatch($user, config('auth.defaults.guard'), config('bifrost.remember_user', true));

        // Set notification if there is a flash notifier
        $this->notify('Welcome ' . $user->name);

        if (session()->has('url.intended')) {
            return redirect()->intended();
        }

        return $this->resolveRedirect('bifrost.redirects.after_login');
    }

    /**
     * Logout the user
     */
    public function logout()
    {
        // Logout user
        Auth::logout();

        // Set notification if there is a flash notifier
        $this->notify('You have logout successfully');

        return $this->resolveRedirect('bifrost.redirects.after_logout');
    }
}
