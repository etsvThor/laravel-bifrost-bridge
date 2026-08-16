<?php

namespace EtsvThor\BifrostBridge;

use EtsvThor\BifrostBridge\Data\BifrostUserData;
use EtsvThor\BifrostBridge\Enums\Intended;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use Override;

/**
 * @property \Laravel\Socialite\Contracts\User|null $user
 */
class BifrostSocialiteProvider extends AbstractProvider
{
    /**
     * @inheritdoc
     */
    protected $scopes;

    /**
     * @inheritdoc
     */
    protected $scopeSeparator = ' ';

    /**
     * @inheritdoc
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        // get scopes from config and set them
        $this->setScopes(
            $this->getConfig('scopes', [])
        );
    }

    public function intended(Intended | null $intended = null): self
    {
        Arr::set($this->parameters, 'intended', ($intended ?? Intended::default())->value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    #[Override]
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getLaravelPassportUrl('authorize_uri'), $state);
    }

    /**
     * @inheritdoc
     */
    #[Override]
    protected function getTokenUrl(): string
    {
        return $this->getLaravelPassportUrl('token_uri');
    }

    /**
     * @inheritdoc
     */
    #[Override]
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get($this->getLaravelPassportUrl('userinfo_uri'), [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * @inheritdoc
     */
    #[Override]
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => 'id',
            'nickname' => 'nickname',
            'name' => 'name',
            'email' => 'email',
            'avatar' => 'avatar',
        ]);
    }

    /**
     * @inheritdoc
     */
    #[Override]
    public function user()
    {
        if ($this->user instanceof BifrostUserData) {
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
     * @inheritdoc
     */
    #[Override]
    protected function getTokenFields($code): array
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    protected function getLaravelPassportUrl(string $type): string
    {
        return rtrim($this->getConfig('host'), '/').'/'.ltrim(($this->getConfig($type, Arr::get([
            'authorize_uri' => 'oauth/authorize',
            'token_uri' => 'oauth/token',
            'userinfo_uri' => 'api/user',
        ], $type))), '/');
    }

    protected function getConfig(string $key, string | array | null $default = null): mixed
    {
        return config('bifrost.service.'.$key, $default);
    }
}
