<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects\Collections;

use EtsvThor\BifrostBridge\DataTransferObjects\BifrostRoleData;
use EtsvThor\BifrostBridge\DataTransferObjects\Casters\BifrostRoleDataCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class BifrostRoleDataCollection extends DataTransferObject
{
    /** @var BifrostRoleData[] */
    #[CastWith(ArrayCaster::class, itemType: BifrostRoleData::class)]
    public ?array $roles;
}
