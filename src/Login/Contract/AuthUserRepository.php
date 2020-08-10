<?php

namespace Upbond\Auth\Login\Contract;

use \Illuminate\Contracts\Auth\Authenticatable;

interface AuthUserRepository
{
    /**
     * @param array $decodedJwt with the data provided in the JWT
     *
     * @return Authenticatable
     */
    public function getUserByDecodedJWT(array $decodedJwt) : Authenticatable;

    /**
     * @param array $userInfo representing the user profile and user accessToken
     *
     * @return Authenticatable
     */
    public function getUserByUserInfo(array $userInfo) : Authenticatable;

    /**
     * @param string|int|null $identifier the user id
     *
     * @return Authenticatable|null
     */
    public function getUserByIdentifier($identifier) : ?Authenticatable;
}
