<?php

namespace Upbond\Auth\Login;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Upbond\Auth\Login\Contract\AuthUserRepository;
use Upbond\Auth\SDK\Exception\CoreException;
use Upbond\Auth\SDK\Exception\InvalidTokenException;

/**
 * Service that provides an Upbond\Auth\LaravelAuth\AuthUser stored in the session. This User provider
 * should be used when you don't want to persist the entity.
 */
class AuthUserProvider implements UserProvider
{
    protected $userRepository;
    protected $auth;

    /**
     * AuthUserProvider constructor.
     *
     * @param AuthUserRepository       $userRepository
     * @param \Auth\Login\AuthService $auth
     */
    public function __construct(AuthUserRepository $userRepository, AuthService $auth)
    {
        $this->userRepository = $userRepository;
        $this->auth = $auth;
    }

    /**
     * Lets make the repository take care of returning the user related to the
     * identifier.
     *
     * @param mixed $identifier
     *
     * @return Authenticatable
     */
    public function retrieveByID($identifier)
    {
        return $this->userRepository->getUserByIdentifier($identifier);
    }

    /**
     * @param array $credentials
     *
     * @return bool|Authenticatable
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['api_token'])) {
            return null;
        }

        $encUser = $credentials['api_token'];

        try {
            $decodedJWT = $this->auth->decodeJWT($encUser);
        } catch (CoreException $e) {
            return null;
        } catch (InvalidTokenException $e) {
            return null;
        }

        return $this->userRepository->getUserByDecodedJWT($decodedJWT);
    }

    /**
     * Required method by the UserProviderInterface, we don't implement it.
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Required method by the UserProviderInterface, we don't implement it.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /**
     * Required method by the UserProviderInterface, we don't implement it.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return null;
    }
}
