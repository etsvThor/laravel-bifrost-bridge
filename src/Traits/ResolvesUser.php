<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use EtsvThor\BifrostBridge\BifrostBridge;
use EtsvThor\BifrostBridge\DataTransferObjects\BifrostUserData;

trait ResolvesUser
{
    /** @var callable|string|null */
    protected static $userResolver = null;

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveAndUpdateUserUsing($callback): void
    {
        static::$userResolver = $callback;
    }

    public static function defaultUserResolver(): Closure
    {
        return function(BifrostUserData $data): ?Model {
            // Try to retrieve the user
            $user = BifrostBridge::getUserClass()->where(BifrostBridge::oauthUserIdKey(), $data->oauth_user_id)->first();

            if (is_null($user)) {
                // See if the user has an e-mailaddress
                if (count($data->all_emails) < 1) {
                    return null;
                }

                // There is an email, so find the user. Either from array of emails or single email value
                $user = BifrostBridge::applyWithTrashed()
                    ->whereIn(BifrostBridge::emailKey(), $data->all_emails)
                    ->first();

                // Nope, create a user
                if (is_null($user)) {
                    $user = BifrostBridge::getUserClass()::forceCreate([
                        BifrostBridge::oauthUserIdKey() => $data->oauth_user_id,
                        BifrostBridge::nameKey()  => $data->name,
                        BifrostBridge::emailKey() => $data->email,
                        BifrostBridge::emailVerifiedAtKey() => $data->email_verified_at,
                    ]);
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

            // If the user has new info, force update it
            if (Carbon::parse($data->updated_at)->greaterThan($user->{$user->getUpdatedAtColumn()})) {
                $user->forceFill([
                    BifrostBridge::oauthUserIdKey() => $data->oauth_user_id,
                    BifrostBridge::nameKey()  => $data->name,
                    BifrostBridge::emailKey() => $data->email,
                    BifrostBridge::emailVerifiedAtKey() => $data->email_verified_at,
                ]);
                $user->save();
            }

            // Sync roles if applicable
            if (! is_null($data->roles) && ! is_null($roleClass = BifrostBridge::getRoleClass())) {
                if (config('bifrost.auto_assign', false)) {
                    // Retrieve all system roles
                    $allRoles = BifrostBridge::getRoleClass()->with('users')->get();

                    // Retrieve current roles
                    $existingRoles = $user->getRoleNames();

                    // Sync roles that exist on this system
                    $newRoles = collect($data->roles);

                    // Calculate which roles to attach
                    $toAttach = $newRoles
                        ->diff($existingRoles)
                        ->map(fn ($roleName) => optional($allRoles->where('name', $roleName)->first())->getKey())
                        ->filter();

                    // Calculate which roles to detach, only detach auto assigned roles
                    $toDetach = $user->roles->whereIn('name', $existingRoles->diff($newRoles)->all())
                        ->where('pivot.auto_assigned', 1);

                    // Auto attach role
                    if ($toAttach->count() > 0) {
                        $user->roles()->attach($toAttach, ['auto_assigned' => 1]);
                    }

                    // Auto detach role
                    if ($toDetach->count() > 0) {
                        $user->roles()->detach($toDetach);
                    }
                } else {
                    // Sync roles that exist on this system
                    $roles = $roleClass::whereIn('name', $data->roles)->get();

                    // Force roles on this user
                    $user->syncRoles($roles);
                }
            }

            return $user;
        };
    }
}
