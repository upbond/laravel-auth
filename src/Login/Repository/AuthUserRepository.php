<?php

namespace Upbond\Auth\Login\Repository;

use Upbond\Auth\Login\AuthUser;
use Upbond\Auth\Login\AuthJWTUser;
use Upbond\Auth\Login\Contract\AuthUserRepository as AuthUserRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Upbond\Auth\Login\AuthService;
use Upbond\Auth\Login\LaravelSessionStore;

class AuthUserRepository implements AuthUserRepositoryContract
{
    /**
     * @param array $decodedJwt
     *
     * @return AuthJWTUser
     */
    public function getUserByDecodedJWT(array $decodedJwt) : Authenticatable
    {
        $userClass = config('upbond.user.api');
        return new $userClass($decodedJwt);
    }

    /**
     * @param array $userInfo
     *
     * @return AuthUser
     */
    public function getUserByUserInfo(array $userInfo) : Authenticatable
    {
        $userClass = config('upbond.user.session');
        return new $userClass($userInfo['profile'], $userInfo['accessToken'], $userInfo['account']);
    }

    /**
     * @param string|int|null $identifier
     *
     * @return Authenticatable|null
     */
    public function getUserByIdentifier($identifier) : ?Authenticatable
    {
        // Get the user info of the user logged in (probably in session)
        
        if ($domain = (new LaravelSessionStore())->get('domain')) {
            $config = array_merge(config('upbond'), [
                'domain' =>  $domain,
                'client_id' => (new LaravelSessionStore())->get('client'),
                'redirect_uri' =>  (new LaravelSessionStore())->get('redirect'),
            ]);
            $auth = new AuthService($config);
    
        }else{
            $auth = \App::make('upbond');
        }

        $user = $auth->getUser();
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
