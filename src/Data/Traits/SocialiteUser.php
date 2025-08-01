<?php

namespace EtsvThor\BifrostBridge\Data\Traits;

trait SocialiteUser
{
    public string | null $token = null;

    public string | null $refreshToken = null;

    public int | null $expiresIn = null;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refreshToken
     * @return $this
     */
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param  int  $expiresIn
     * @return $this
     */
    public function setExpiresIn(int $expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return string | int
     */
    public function getId()
    {
        return $this->oauth_user_id;
    }

    /**
     * Get the nickname / username for the user.
     *
     * @return string | null
     */
    public function getNickname()
    {
        return null;
    }

    /**
     * Get the full name of the user.
     *
     * @return string | null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string | null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string | null
     */
    public function getAvatar()
    {
        return null;
    }
}
