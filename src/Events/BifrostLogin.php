<?php

namespace EtsvThor\BifrostBridge\Events;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Events\Dispatchable;

class BifrostLogin extends Login
{
    use Dispatchable;
}
