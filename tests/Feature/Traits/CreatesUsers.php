<?php

namespace EtsvThor\BifrostBridge\Tests\Feature\Traits;

use EtsvThor\BifrostBridge\Tests\Fixtures\User;

trait CreatesUsers
{
    /**
     * Create a user with the given oauth user id.
     */
    protected function createUser(int $oauthUserId, array $attributes = []): User
    {
        return User::query()->forceCreate(array_merge([
            'name' => 'User '.$oauthUserId,
            'email' => 'user'.$oauthUserId.'@example.com',
            'email_verified_at' => now(),
            'oauth_user_id' => $oauthUserId,
        ], $attributes));
    }
}
