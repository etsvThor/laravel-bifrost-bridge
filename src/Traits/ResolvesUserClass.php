<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;

trait ResolvesUserClass
{
    /** @var callable|string|null */
    protected static $userClassResolver = null;

    public static function resolveUserClassUsing(callable | string | null $callback): void
    {
        static::$userClassResolver = $callback;
    }

    public static function defaultUserClassResolver(): Closure
    {
        return fn () => app(config('bifrost.user.model', 'App\\Models\\User'));
    }
}
