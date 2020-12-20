<?php

namespace EtsvThor\BifrostBridge\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Facades\Socialite;
use EtsvThor\BifrostBridge\BifrostBridge;
use EtsvThor\BifrostBridge\DataTransferObjects\BifrostUserData;
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
            $user = BifrostBridge::getUserClass()::whereKey($request->get('id', 1))->firstOrFail();

            // Login user
            Auth::login($user);
            
            $this->notify($user->name . ' has been logged in automatically, as Bifrost is disabled');

            return $this->resolveRedirect('bifrost.redirects.after_login');
        }

        return Socialite::driver('laravelpassport')->redirect();
    }

    protected function retrieveOrCreateUser(BifrostUserData $data): ?Model
    {
        // Try to retrieve the user
        $user = BifrostBridge::getUserClass()->where(BifrostBridge::oauthUserIdKey(), $data->oauth_user_id)->first();

        if (is_null($user)) {
            // See if the user has an e-mailaddress
            if (is_null($data->emails) && is_null($data->email)) {
                return BifrostBridge::retrieveUserWithoutEmail($data);
            }

            // There is an email, so find the user. Either from array of emails or single email value
            if(is_null($data->emails)) {
                $user = BifrostBridge::applyWithTrashed()->where(BifrostBridge::emailKey(), $data->email)->first();
            } else {
                $user = BifrostBridge::applyWithTrashed()->whereIn(BifrostBridge::emailKey(), $data->emails)->first();
            }

            // Nope, create a user
            if (is_null($user)) {
                $user = BifrostBridge::getUserClass()::forceCreate($data->except('roles', 'created_at', 'updated_at')->toArray());
            }

            // Check if we need to verify email
            if (BifrostBridge::isVerifyingEmail($user) && ! $user->hasVerifiedEmail()) {
                return null;
            }

            // Check if the user is deleted
            if (BifrostBridge::isSoftDeletable($user) && $user->trashed()) {
                return null;
            }
        }

        return $user;
    }

    public function callback()
    {
        /** @var \Laravel\Socialite\AbstractUser $socialiteUser */
        $socialiteUser = Socialite::driver('laravelpassport')->user();
        $data = new BifrostUserData($socialiteUser->getRaw());

        // See if the user exists
        $user = $this->retrieveOrCreateUser($data);

        // If we have no user, something was wrong, either no verified email or deleted account
        if (is_null($user)) {
            abort(403, 'Cannot automatically link your account. Contact system administrator.');
        }

        // If the user has new info, force update it
        if (Carbon::parse($data->updated_at)->greaterThan($user->updated_at)) {
            $user->forceFill($data->except('roles', 'created_at', 'updated_at')->toArray());
            $user->save();
        }

        // Sync roles if applicable
        if (! is_null($roleClass = BifrostBridge::getRoleClass())) {
            // Sync roles that exist on this system
            $roles = $roleClass::whereIn('name', $data->roles)->get();

            // Force roles on this user
            $user->syncRoles($roles);
        }

        // Login user
        Auth::login($user);

        // Set notification if there is a flash notifier
        $this->notify('Welcome ' . $user->name);

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
