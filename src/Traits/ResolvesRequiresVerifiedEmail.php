<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;

trait ResolvesRequiresVerifiedEmail
{
    /** @var callable|string|null */
    protected static $requiresVerifiedEmailResolver = null;

    public static function resolveRequiresVerifiedEmailUsing(callable | string | null $callback): void
    {
        static::$requiresVerifiedEmailResolver = $callback;
    }

    public static function defaultRequiresVerifiedEmailResolver(): Closure
    {
        return fn () => app(config('bifrost.user.requires_verified_email', true));
    }
}
