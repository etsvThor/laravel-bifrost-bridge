<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\Enums\Intended;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use EtsvThor\BifrostBridge\Data\BifrostUserData;

class BifrostSocialiteProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected $scopes;

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        // get scopes from config and set them
        $this->setScopes(
            $this->getConfig('scopes', [])
        );
    }

    public function intended(Intended $intended = null): self
    {
        Arr::set($this->parameters, 'intended', ($intended ?? Intended::default())?->value);
        return $this;
    }

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
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => 'id',
            'nickname' => 'nickname',
            'name'     => 'name',
            'email'    => 'email',
            'avatar'   => 'avatar',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->user && $this->user instanceof BifrostUserData) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $userData = $this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        );

        if (array_key_exists('id', $userData)) {
            $userData['oauth_user_id'] = $userData['id'];
            unset($userData['id']);
        }

        $this->user = BifrostUserData::from($userData);

        return $this->user->setToken($token)
                    ->setRefreshToken(Arr::get($response, 'refresh_token'))
                    ->setExpiresIn(Arr::get($response, 'expires_in'));
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
