<?php

namespace Upbond\Auth\Login;

/**
 * This class represents a generic user initialized with the user information
 * given by Auth and provides a way to access to the user profile.
 */
class AuthUser implements \Illuminate\Contracts\Auth\Authenticatable
{
    protected $userInfo;
    protected $accessToken;
    protected $account;

    /**
     * AuthUser constructor.
     *
     * @param array $userInfo
     * @param string|null $accessToken
     */
    public function __construct(array $userInfo, $accessToken, $account)
    {
        $this->userInfo = $userInfo;
        $this->accessToken = $accessToken;
        $this->account = $account;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
      if (isset($this->userInfo['sub'])) {
        return $this->userInfo['sub'];
      }
      return $this->userInfo['id'];
    }

    /**
     * Get id field name.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->accessToken;
    }
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return void
     */
    public function getRememberToken()
    {
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
    }

    /**
     * @return void
     */
    public function getRememberTokenName()
    {
    }
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Add a generic getter to get all the properties of the userInfo.
     *
     * @return the related value or null if it is not set
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->userInfo)) {
            return;
        }

        return $this->userInfo[$name];
    }

    /**
     * @return mixed
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->userInfo);
    }
}
