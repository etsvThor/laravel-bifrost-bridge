<?php

namespace EtsvThor\BifrostBridge\Data;

use Laravel\Socialite\Contracts\User;
use Spatie\LaravelData\Data;

class BifrostUserData extends Data implements User
{
    use Traits\SocialiteUser;

    public function __construct(
        public int     $oauth_user_id,
        public string  $name,
        public string  $created_at,
        public string  $updated_at,
        public ?string $email = null,
        public ?string $email_verified_at = null,
        public ?array  $alternate_emails = [],
        public array   $roles = [],
    )
    {}

    /**
     * @return array<int, string>
     */
    public function allEmails(): array
    {
        return collect([$this->email])->merge($this->alternate_emails ?? [])->filter()->all();
    }
}
