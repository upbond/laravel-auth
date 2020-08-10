<?php

namespace Upbond\Auth\Login;

/**
 * This class represents a generic user initialized with the user information
 * given by Auth and provides a way to access to the decoded JWT data.
 */
class AuthJWTUser implements \Illuminate\Contracts\Auth\Authenticatable
{
    private $userInfo;

    /**
     * AuthJWTUser constructor.
     *
     * @param $userInfo
     */
    public function __construct(array $userInfo)
    {
        $this->userInfo = $userInfo;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierName()
    {
        return $this->userInfo['sub'];
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->userInfo['sub'];
    }

    /**
     * @return void
     */
    public function getAuthPassword()
    {
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

    /**
     * Add a generic getter to get all the properties of the userInfo.
     *
     * @return mixed the related value or null if it is not set
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->userInfo)) {
            return;
        }

        return $this->userInfo[$name];
    }

    /**
     * @return array
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
