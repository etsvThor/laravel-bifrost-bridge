<?php

namespace EtsvThor\BifrostBridge\Tests\Fixtures;

use EtsvThor\BifrostBridge\Contracts\BifrostUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements BifrostUser, MustVerifyEmail
{
    use HasRoles {
        roles as protected spatieRoles;
    }
    use SoftDeletes;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Expose the auto_assigned pivot, which the bridge relies on when
     * config('bifrost.auto_assign') is enabled.
     */
    public function roles(): BelongsToMany
    {
        return $this->spatieRoles()->withPivot('auto_assigned');
    }
}
