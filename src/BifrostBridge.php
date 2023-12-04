<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\Data\BifrostUserData;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;

class BifrostBridge
{
    use Traits\ResolvesRoleClass, Traits\ResolvesUserClass, Traits\ResolvesUser;

    // Resolvers
    public static function resolveAndUpdateUser(BifrostUserData $data): ?Model
    {
        return app()->call(static::$userResolver ?? static::defaultUserResolver(), ['data' => $data]);
    }

    public static function getUserClass(): Model
    {
        return app()->call(static::$userClassResolver ?? static::defaultUserClassResolver());
    }

    public static function getRoleClass(): ?Role
    {
        return app()->call(static::$roleClassResolver ?? static::defaultRoleClassResolver());
    }

    // Keys
    public static function oauthUserIdKey(): string
    {
        return config('bifrost.user.oauth_user_id_key', 'oauth_user_id');
    }

    public static function emailKey(): string
    {
        return config('bifrost.user.email_key', 'email');
    }

    public static function emailVerifiedAtKey(): string
    {
        return config('bifrost.user.email_verified_at_key', 'email_verified_at');
    }

    public static function nameKey(): string
    {
        return config('bifrost.user.name_key', 'name');
    }

    // Helpers
    public static function isSoftDeletable(Model $model = null): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model ?? static::getUserClass())) === true;
    }

    public static function isVerifyingEmail(Model $model = null): bool
    {
        return (($model ?? static::getUserClass()) instanceof MustVerifyEmail);
    }

    public static function applyWithTrashed(Model $model = null): Builder
    {
        return static::isSoftDeletable($model ??= static::getUserClass())
            ? $model->withTrashed()
            : $model->query();
    }
}
