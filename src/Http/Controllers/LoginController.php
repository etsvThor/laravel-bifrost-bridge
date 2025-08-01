<?php

namespace EtsvThor\BifrostBridge\Http\Controllers;

use Error;
use EtsvThor\BifrostBridge\BifrostSocialiteProvider;
use EtsvThor\BifrostBridge\Data\BifrostUserData;
use EtsvThor\BifrostBridge\Enums\Intended;
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

    private function tryFlashNotification(string $message, string $type = 'success'): bool
    {
        try {
            app('flash')->message($message, $type);
        } catch (BindingResolutionException) {
            return false;
        }

        return true;
    }

    private function tryFilamentNotification(string $message, string $type = 'success'): bool
    {
        $class = 'Filament\\Notifications\\Notification';
        if (! class_exists($class)) {
            return false;
        }

        $notification = $class::make()
            ->body($message);

        $notification = match($type) {
            'success' => $notification->success(),
            'error' => $notification->danger(),
            'warning' => $notification->warning(),
            default => $notification,
        };

        $notification->send();

        return true;
    }

    protected function notify(string $message, string $type = 'success'): bool
    {
        return $this->tryFlashNotification($message, $type) || $this->tryFilamentNotification($message, $type);
    }


    public function redirect(Request $request)
    {
        if (config('bifrost.enabled') !== true) {
            if (! App::environment('local')) {
                abort(500, 'Bifrost is not enabled, authentication is not possible');
            }

            // allow login without password for LOCAL environments when bifrost is NOT enabled
            if($request->has('id')) {
                $user = BifrostBridge::getUserClass()::query()->whereKey($request->get('id', 1))->firstOrFail();
            } else {
                $user = BifrostBridge::getUserClass()::query()->firstOrFail();
            }

            // Login user
            Auth::login($user, config('bifrost.remember_user', true)); // @phpstan-ignore argument.type

            $this->notify(($user->getAttribute('name') ?? 'The first user') . ' has been logged in automatically, as Bifrost is disabled');

            return $this->resolveRedirect('bifrost.redirects.after_login');
        }

        $intended = $request->get('intended', config('bifrost.service.intended', 'login'));

        /** @var \EtsvThor\BifrostBridge\BifrostSocialiteProvider */
        $bifrost = Socialite::driver('bifrost');

        return $bifrost
            ->intended(Intended::from($intended))
            ->redirect();
    }

    public function callback()
    {
        /** @var \EtsvThor\BifrostBridge\Data\BifrostUserData $data */
        $data = Socialite::driver('bifrost')->user();

        // See if the user exists
        $user = BifrostBridge::resolveAndUpdateUser($data);

        // If we have no user, something was wrong, either no verified email or deleted account
        if (is_null($user)) {
            abort(403, 'Cannot automatically link your account. Contact system administrator.');
        }

        // Login user
        Auth::login($user, config('bifrost.remember_user', true));  // @phpstan-ignore argument.type
        BifrostLogin::dispatch($user, config('auth.defaults.guard'), config('bifrost.remember_user', true));

        // Set notification if there is a flash notifier
        $this->notify('Welcome ' . ($user->getAttribute('name') ?? ''));

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
