<?php

namespace EtsvThor\BifrostBridge\Data;

use Laravel\Socialite\Contracts\User;
use Spatie\LaravelData\Data;

class BifrostUserData extends Data implements User
{
    use Traits\SocialiteUser;

    public function __construct(
        public int $oauth_user_id,
        public string $name,
        public string $created_at,
        public string $updated_at,
        public ?string $email = null,
        public ?string $email_verified_at = null,
        /**
         * @var string[] | null
         */
        public ?array $alternate_emails = [],
        /**
         * @var string[]
         */
        public array $roles = [],
        public ?int $member_id = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function allEmails(): array
    {
        return array_values(array_filter([
            $this->email,
            ...($this->alternate_emails ?? []),
        ]));
    }
}
