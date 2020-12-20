<?php

namespace EtsvThor\BifrostBridge\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class BifrostUserData extends DataTransferObject
{
    public int     $oauth_user_id;
    public string  $name;
    public ?string $email = null;
    public ?string $email_verified_at = null;
    public string  $created_at;
    public string  $updated_at;
    public array   $roles = [];
    public ?array  $emails = [];

    public function __construct(array $parameters = [])
    {
        if (array_key_exists('id', $parameters)) {
            $parameters['oauth_user_id'] = $parameters['id'];
            unset($parameters['id']);
        }

        parent::__construct($parameters);
    }
}
