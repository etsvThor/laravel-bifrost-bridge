<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\DataTransferObjects\BifrostUserData;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;

class BifrostBridge
{
    /** @var callable|string|null */
    protected static $userClassResolver = null;

    /** @var callable|string|null */
    protected static $roleClassResolver = null;

    /** @var callable|string|null */
    protected static $userWithoutEmailResolver = null;

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveUserClassUsing($callback): void
    {
        static::$userClassResolver = $callback;
    }

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveRoleClassUsing($callback): void
    {
        static::$roleClassResolver = $callback;
    }

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveUserWithoutEmailUsing($callback): void
    {
        static::$userWithoutEmailResolver = $callback;
    }

    public static function retrieveUserWithoutEmail(BifrostUserData $data): ?Model
    {
        if (! is_null(static::$userWithoutEmailResolver)) {
            return app()->call(static::$userWithoutEmailResolver, [$data]);
        }

        return null;
    }

    public static function getUserClass(): Model
    {
        if (! is_null(static::$userClassResolver)) {
            return app()->call(static::$userClassResolver);
        }

        return app(config('bifrost.user.model', 'App\\Models\\User'));
    }

    public static function getRoleClass(): ?Role
    {
        if (! is_null(static::$roleClassResolver)) {
            return app()->call(static::$roleClassResolver);
        }

        return app(PermissionRegistrar::class)->getRoleClass();
    }

    public static function oauthUserIdKey(): string
    {
        return config('bifrost.user.oauth_user_id_key', 'oauth_user_id');
    }

    public static function emailKey(): string
    {
        return config('bifrost.user.email_key', 'email');
    }

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
