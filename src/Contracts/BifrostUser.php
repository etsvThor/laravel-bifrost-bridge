<?php

namespace EtsvThor\BifrostBridge\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

interface BifrostUser extends Authenticatable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Spatie\Permission\Models\Role, static & \Illuminate\Database\Eloquent\Model>
     */
    public function roles(): BelongsToMany;

    /**
     * @param  string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection|\BackedEnum  ...$roles
     * @return $this
     */
    public function syncRoles(...$roles); // @phpstan-ignore missingType.generics

    /**
     * @return \Illuminate\Support\Collection<array-key, string>
     */
    public function getRoleNames(): Collection;
}
