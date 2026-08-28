<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;
use Spatie\Permission\PermissionRegistrar;

trait ResolvesRoleClass
{
    /** @var callable|string|null */
    protected static $roleClassResolver = null;

    public static function resolveRoleClassUsing(callable | string | null $callback): void
    {
        static::$roleClassResolver = $callback;
    }

    public static function defaultRoleClassResolver(): Closure
    {
        return function () {
            $class = app(PermissionRegistrar::class)->getRoleClass();

            return is_string($class) ? app($class) : $class; // @phpstan-ignore function.alreadyNarrowedType
        };
    }
}
