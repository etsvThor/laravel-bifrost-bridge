<?php

namespace EtsvThor\BifrostBridge\Jobs;

use EtsvThor\BifrostBridge\BifrostBridge;
use EtsvThor\BifrostBridge\DataTransferObjects\Collections\BifrostRoleDataCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookBifrost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BifrostRoleDataCollection $roles;

    public function __construct(BifrostRoleDataCollection $roles)
    {
        $this->roles = $roles;
    }

    public function handle(): void
    {
        // Check if roles are enabled
        if (is_null(BifrostBridge::getRoleClass())) {
            Log::warning('No role class found, but bifrost auth push triggered.');
            return;
        }

        // Retrieve all system roles
        $allRoles = BifrostBridge::getRoleClass()->with('users')->get();

        $oauthUserId = BifrostBridge::oauthUserIdKey();
        $userClassKey = BifrostBridge::getUserClass()->getKeyName();

        foreach ($this->roles as $bifrostRole) {
            $systemRole = $allRoles->where('name', $bifrostRole->name)->first();

            // Role does not exist, by default, don't create a role
            if (is_null($systemRole)) {
                continue;
            }

            // Get old and new user collection for this role
            $newUsers = collect($bifrostRole->users);
            $oldUsers = $systemRole->users->pluck($oauthUserId)->filter();

            if (config('bifrost.auto_assign', false)) {
                // Get users who do not have this role auto assigned
                $notAutoAssignedUsers = $systemRole->users->where('pivot.auto_assigned', 0)->pluck($userClassKey);
            } else {
                $notAutoAssignedUsers = [];
            }

            // See who needs to be attached and detached
            $attach = BifrostBridge::getUserClass()::whereIn($oauthUserId, $newUsers->diff($oldUsers))
                ->whereNotIn($userClassKey, $notAutoAssignedUsers) // Do not attach if this role is not auto assigned
                ->pluck($userClassKey);

            $detach = BifrostBridge::getUserClass()::whereIn($oauthUserId, $oldUsers->diff($newUsers))
                ->whereNotIn($userClassKey, $notAutoAssignedUsers) // Do not detach if this role is not auto assigned
                ->pluck($userClassKey);

            // Attach if needed
            if ($attach->count() > 0) {
                if (config('bifrost.auto_assign', false)) {
                    $systemRole->users()->attach($attach, ['auto_assigned' => 1]);
                }

                $systemRole->users()->attach($attach);
                Log::debug('Attached ' . $systemRole->name . ' to users: ' . $attach->implode(', '));
            }

            // Detach if needed
            if ($detach->count() > 0) {
                $systemRole->users()->detach($detach);
                Log::debug('Detached ' . $systemRole->name . ' from users: ' . $detach->implode(', '));
            }
        }

        if (config('bifrost.auth_push_detach_on_remove') === true) {
            // If a role is not present on Bifrost anymore, remove all users from it.
            $existingOnSystemButNotBifrost = $allRoles->whereNotIn('name', collect($this->roles)->pluck('name'));
            foreach ($existingOnSystemButNotBifrost as $role) {
                $role->users()->detach(); // detach all users, but keep the role
                Log::info('Role ' . $role->name . ' was removed on Bifrost. Detached all users.');
            }
        }
    }
}
