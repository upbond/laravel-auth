<?php

namespace Upbond\Auth\Login\Repository;

use Upbond\Auth\Login\AuthUser;
use Upbond\Auth\Login\AuthJWTUser;
use Upbond\Auth\Login\Contract\AuthUserRepository as AuthUserRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthUserRepository implements AuthUserRepositoryContract
{
    /**
     * @param array $decodedJwt
     *
     * @return AuthJWTUser
     */
    public function getUserByDecodedJWT(array $decodedJwt) : Authenticatable
    {
        return new AuthJWTUser($decodedJwt);
    }

    /**
     * @param array $userInfo
     *
     * @return AuthUser
     */
    public function getUserByUserInfo(array $userInfo) : Authenticatable
    {
        return new AuthUser($userInfo['profile'], $userInfo['accessToken'], $userInfo['account']);
    }

    /**
     * @param string|int|null $identifier
     *
     * @return Authenticatable|null
     */
    public function getUserByIdentifier($identifier) : ?Authenticatable
    {
        // Get the user info of the user logged in (probably in session)
        $user = \App::make('upbond')->getUser();

        if ($user === null) {
            return null;
        }

        // Build the user
        $upbondUser = $this->getUserByUserInfo($user);

        // It is not the same user as logged in, it is not valid
        if ($upbondUser && $upbondUser->getAuthIdentifier() == $identifier) {
            return $upbondUser;
        }
    }
}
