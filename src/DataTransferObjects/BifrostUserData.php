<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class BifrostUserData extends DataTransferObject
{
    public int     $oauth_user_id;
    public string  $name;
    public ?string $email = null;
    public ?string $email_verified_at = null;
    public ?array  $alternate_emails = [];
    public string  $created_at;
    public string  $updated_at;
    public array   $roles = [];

    public array $all_emails = [];

    public function __construct(array $parameters = [])
    {
        if (array_key_exists('id', $parameters)) {
            $parameters['oauth_user_id'] = $parameters['id'];
            unset($parameters['id']);
        }

        parent::__construct($parameters);

        if (! is_null($this->email)) {
            $this->all_emails = [$this->email];
        }

        if (! is_null($this->alternate_emails)) {
            $this->all_emails = array_merge($this->all_emails, $this->alternate_emails);
        }
    }
}
