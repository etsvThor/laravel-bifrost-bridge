<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;
use Spatie\Permission\PermissionRegistrar;

trait ResolvesRoleClass
{
    /** @var callable|string|null */
    protected static $roleClassResolver = null;

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveRoleClassUsing($callback): void
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
