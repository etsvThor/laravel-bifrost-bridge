<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\DataTransferObjects\BifrostUserData;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;

class BifrostSocialiteProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected $scopes = [''];

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getLaravelPassportUrl('authorize_uri'), $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->getLaravelPassportUrl('token_uri');
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     *
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getLaravelPassportUrl('userinfo_uri'), [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return \EtsvThor\BifrostBridge\DataTransferObjects\BifrostUserData
     */
    protected function mapUserToObject(array $user)
    {
        return new BifrostUserData($user);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    protected function getLaravelPassportUrl($type)
    {
        return rtrim($this->getConfig('host'), '/').'/'.ltrim(($this->getConfig($type, Arr::get([
            'authorize_uri' => 'oauth/authorize',
            'token_uri'     => 'oauth/token',
            'userinfo_uri'  => 'api/user',
        ], $type))), '/');
    }

    protected function getConfig($key, $default = null)
    {
        return config('bifrost.service.'.$key, $default);
    }
}
