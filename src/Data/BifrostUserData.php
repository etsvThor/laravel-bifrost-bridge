<?php

namespace EtsvThor\BifrostBridge\Data;

use Laravel\Socialite\Contracts\User;
use Spatie\LaravelData\Data;

class BifrostUserData extends Data implements User
{
    use Traits\SocialiteUser;

    /**
     * @var string[]
     */
    public array $all_emails = [];

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
    {
        if (! is_null($this->email)) {
            $this->all_emails = [$this->email];
        }

        if (! is_null($this->alternate_emails)) {
            $this->all_emails = array_merge($this->all_emails, $this->alternate_emails);
        }
    }
}
