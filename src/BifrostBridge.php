<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\Data\BifrostUserData;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BifrostBridge
{
    use Traits\ResolvesRoleClass, Traits\ResolvesUserClass, Traits\ResolvesUser, Traits\ResolvesRequiresVerifiedEmail;

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

    public static function requiresVerifiedEmail(): bool
    {
        return app()->call(static::$requiresVerifiedEmailResolver ?? static::defaultRequiresVerifiedEmailResolver());
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

    public static function memberIdKey(): ?string
    {
        return config('bifrost.user.member_id_key'); // no default to prevent breaking change
    }

    // Helpers
    public static function isSoftDeletable(Model | null $model = null): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model ?? static::getUserClass())) === true;
    }

    public static function isVerifyingEmail(Model | null $model = null): bool
    {
        return ($model ?? static::getUserClass()) instanceof MustVerifyEmail;
    }

    public static function applyWithTrashed(Model | null $model = null): Builder
    {
        return static::isSoftDeletable($model ??= static::getUserClass())
            ? $model->withTrashed() // @phpstan-ignore method.notFound
            : $model->query();
    }
}
