<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects\Collections;

use EtsvThor\BifrostBridge\DataTransferObjects\BifrostRoleData;
use Spatie\DataTransferObject\DataTransferObjectCollection;

class BifrostRoleDataCollection extends DataTransferObjectCollection
{
    public static function create(array $data): self
    {
        return new static(BifrostRoleData::arrayOf($data));
    }

    public function current(): BifrostRoleData
    {
        return parent::current();
    }
}
