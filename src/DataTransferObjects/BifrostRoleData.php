<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class BifrostRoleData extends DataTransferObject
{
    public int $id;
    public string $name;
    public array  $users = [];
}
