<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects\Collections;

use EtsvThor\BifrostBridge\DataTransferObjects\BifrostRoleData;
use Spatie\DataTransferObject\DataTransferObject;

class BifrostRoleDataCollection extends DataTransferObject
{
    public static function create(array $data): self
    {
        return new static(BifrostRoleData::arrayOf($data));
    }
}
