<?php

namespace Upbond\Auth\Login;

use Upbond\Auth\SDK\Auth;
use Upbond\Auth\SDK\Exception\InvalidTokenException;
use Upbond\Auth\SDK\Helpers\JWKFetcher;
use Upbond\Auth\SDK\Helpers\UserFetcher;
use Upbond\Auth\SDK\Helpers\Tokens\AsymmetricVerifier;
use Upbond\Auth\SDK\Helpers\Tokens\AsymmetricUpbondVerifier;
use Upbond\Auth\SDK\Helpers\Tokens\SymmetricVerifier;
use Upbond\Auth\SDK\Helpers\Tokens\TokenVerifier;
use Upbond\Auth\SDK\Helpers\Tokens\UpbondTokenVerifier;
use Upbond\Auth\SDK\Store\StoreInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\RedirectResponse;
use Psr\SimpleCache\CacheInterface;

/**
 * Service that provides access to the Auth SDK.
 */
class AuthService
{
    /**
     * @var Auth
     */
    private $auth;

    private $apiuser;
    private $_onLoginCb = null;
    private $rememberUser = false;
    private $authConfig = [];

    /**
     * AuthService constructor.
     *
     * @param array|null $authConfig
     * @param StoreInterface|null $store
     * @param CacheInterface|null $cache
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        array $authConfig,
        StoreInterface $store = null,
        CacheInterface $cache = null
    )
    {

        if (!$authConfig instanceof ConfigRepository && !is_array($authConfig)) {
            $authConfig = config('upbond');
        }

        $store = $authConfig['store'] ?? $store;
        if (false !== $store && !$store instanceof StoreInterface) {
            $store = new LaravelSessionStore();
        }
        $authConfig['store'] = $store;

        $cache = $authConfig['cache_handler'] ?? $cache;
        if (!($cache instanceof CacheInterface)) {
            $cache = app()->make('cache.store');
        }
        $authConfig['cache_handler'] = $cache;

        $this->authConfig = $authConfig;
        $this->auth = new Auth($authConfig);
    }

    /**
     * Creates an instance of the Auth SDK using
     * the config set in the laravel way and using a LaravelSession
     * as a store mechanism.
     */
    private function getSDK()
    {
        return $this->auth;
    }

    /**
     * Logs the user out from the SDK.
     */
    public function logout()
    {
        $this->getSDK()->logout();
    }

    /**
     * Redirects the user to the hosted login page
     */
    public function login($connection = null, $state = null, $additional_params = ['scope' => 'openid profile email'], $response_type = 'code')
    {
        if ($connection && empty( $additional_params['connection'] )) {
            $additional_params['connection'] = $connection;
        }

        if ($state && empty( $additional_params['state'] )) {
            $additional_params['state'] = $state;
        }

        $additional_params['response_type'] = $response_type;
        $auth_url = $this->auth->getLoginUrl($additional_params);
        return new RedirectResponse($auth_url);
    }

    /**
     * If the user is logged in, returns the user information.
     *
     * @return array with the User info as described in https://docs.auth.com/user-profile and the user access token
     */
    public function getUser()
    {
        // Get the user info from auth
        $auth = $this->getSDK();
        $user = $auth->getUser();

        if ($user === null) {
            return;
        }

        return [
            'profile' => $user,
            'accessToken' => $auth->getAccessToken(),
        ];
    }

    /**
     * Sets a callback to be called when the user is logged in.
     *
     * @param callback $cb A function that receives an authUser and receives a Laravel user
     */
    public function onLogin($cb)
    {
        $this->_onLoginCb = $cb;
    }

    /**
     * @return bool
     */
    public function hasOnLogin()
    {
        return $this->_onLoginCb !== null;
    }

    /**
     * @param $authUser
     *
     * @return mixed
     */
    public function callOnLogin($authUser)
    {
        return call_user_func($this->_onLoginCb, $authUser);
    }

    /**
     * Use this to either enable or disable the "remember" function for users.
     *
     * @param null $value
     *
     * @return bool|null
     */
    public function rememberUser($value = null)
    {
        if ($value !== null) {
            $this->rememberUser = $value;
        }

        return $this->rememberUser;
    }

    /**
     * @param $encUser
     * @param array $verifierOptions
     *
     * @return array
     * @throws \Auth\SDK\Exception\InvalidTokenException
     */
    public function decodeJWT($encUser, array $verifierOptions = [])
    {
        $token_issuer = 'https://'.$this->authConfig['domain'].'/';
        $apiIdentifier = $this->authConfig['api_identifier'];
        $idTokenAlg = $this->authConfig['supported_algs'][0] ?? 'RS256';
     
        $token_verifier = new UpbondTokenVerifier(
            $token_issuer,
            $apiIdentifier,
            new AsymmetricUpbondVerifier()
        );
        $this->apiuser = $token_verifier->verify($encUser, $verifierOptions);
        
        $user_fetcher = new UserFetcher($token_issuer, $this->authConfig['cache_handler']);
        $user = $user_fetcher->getUser($this->apiuser, $encUser);
        //TODO: accountID 
        // dd($token_issuer);
        $parsedUrl = parse_url($token_issuer);
        $host = explode('.', $parsedUrl['host']);
        $user['account'] = $host[0];
        return $user;

        // $token_issuer = 'https://'.$this->authConfig['domain'].'/';
        // $apiIdentifier = $this->authConfig['api_identifier'];
        // $idTokenAlg = $this->authConfig['supported_algs'][0] ?? 'RS256';
     
        // $signature_verifier = null;
        // if ('RS256' === $idTokenAlg) {
        //     $jwksUri = $this->authConfig['jwks_uri'] ?? 'https://'.$this->authConfig['domain'].'/.well-known/jwks.json';
        //     $jwks_fetcher = new JWKFetcher($this->authConfig['cache_handler']);
        //     $jwks = $jwks_fetcher->getKeys($jwksUri);
        //     $signature_verifier = new AsymmetricVerifier($jwks);
        // } else if ('HS256' === $idTokenAlg) {
        //     $signature_verifier = new SymmetricVerifier($this->authConfig['client_secret']);
        // } else {
        //     throw new InvalidTokenException('Unsupported token signing algorithm configured. Must be either RS256 or HS256.');
        // }

        // // Use IdTokenVerifier since Auth-issued JWTs contain the 'sub' claim, which is used by the Laravel user model
        // $token_verifier = new TokenVerifier(
        //     $token_issuer,
        //     $apiIdentifier,
        //     $signature_verifier
        // );

        // $this->apiuser = $token_verifier->verify($encUser, $verifierOptions);
        // return $this->apiuser;
    }

    public function getIdToken()
    {
        return $this->getSDK()->getIdToken();
    }

    public function getAccessToken()
    {
        return $this->getSDK()->getAccessToken();
    }

    public function getRefreshToken()
    {
        return $this->getSDK()->getRefreshToken();
    }

    public function jwtuser()
    {
        return $this->apiuser;
    }
}
