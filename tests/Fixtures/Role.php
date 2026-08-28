<?php

namespace EtsvThor\BifrostBridge\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Expose the auto_assigned pivot, which the bridge relies on when
     * config('bifrost.auto_assign') is enabled.
     */
    public function users(): BelongsToMany
    {
        return parent::users()->withPivot('auto_assigned');
    }
}
