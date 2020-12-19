<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class BifrostRoleData extends DataTransferObject
{
    public string $name;
    public array  $users = [];
}
