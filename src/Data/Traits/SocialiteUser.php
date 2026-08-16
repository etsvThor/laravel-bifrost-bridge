<?php

namespace EtsvThor\BifrostBridge\Data\Traits;

trait SocialiteUser
{
    public string | null $token = null;

    public string | null $refreshToken = null;

    public int | null $expiresIn = null;

    /**
     * Set the token on the user.
     */
    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     */
    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     */
    public function setExpiresIn(int $expiresIn): static
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getId(): int | string
    {
        return $this->oauth_user_id;
    }

    /**
     * Get the nickname / username for the user.
     */
    public function getNickname(): ?string
    {
        return null;
    }

    /**
     * Get the full name of the user.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the e-mail address of the user.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get the avatar / image URL for the user.
     */
    public function getAvatar(): ?string
    {
        return null;
    }
}
