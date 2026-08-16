<?php

namespace EtsvThor\BifrostBridge\Tests\Feature\Traits;

use EtsvThor\BifrostBridge\Tests\Fixtures\Role;

trait CreatesRoles
{
    /**
     * Create a role by name.
     */
    protected function createRole(string $name): Role
    {
        return Role::query()->create([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }
}
