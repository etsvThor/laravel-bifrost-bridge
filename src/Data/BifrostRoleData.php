<?php

namespace EtsvThor\BifrostBridge\Data;

use Spatie\LaravelData\Data;

class BifrostRoleData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        /**
         * @var int[]
         */
        public array $users = [],
    ) {
    }
}
