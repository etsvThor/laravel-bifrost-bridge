<?php

namespace EtsvThor\BifrostBridge\Traits;

use Closure;

trait ResolvesRequiresVerifiedEmail
{
    /** @var callable|bool|null */
    protected static $requiresVerifiedEmailResolver = null;

    /**
     * @param callable|string|null $callback
     * @return void
     */
    public static function resolveRequiresVerifiedEmailUsing($callback): void
    {
        static::$requiresVerifiedEmailResolver = $callback;
    }

    public static function defaultRequiresVerifiedEmailResolver(): Closure
    {
        return fn() => app(config('bifrost.user.requires_verified_email', true));
    }
}
