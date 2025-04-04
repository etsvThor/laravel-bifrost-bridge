<?php

namespace EtsvThor\BifrostBridge\Enums;

enum Intended: string
{
    case Login = 'login';
    case Register = 'register';

    public static function default(): self
    {
        return self::tryFrom(config('bifrost.service.intended')) ?? self::Login;
    }
}
