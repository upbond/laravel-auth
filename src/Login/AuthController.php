<?php

namespace Upbond\Auth\Login;

use Upbond\Auth\Login\Contract\AuthUserRepository;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    /**
     * @var AuthUserRepository
     */
    protected $userRepository;

    /**
     * AuthController constructor.
     *
     * @param AuthUserRepository $userRepository
     */
    public function __construct(AuthUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Callback action that should be called by upbond, logs the user in.
     */
    public function callback()
    {
        $domain = (new LaravelSessionStore)->get('domain');
        $client = (new LaravelSessionStore)->get('client');

        if ($domain && $client) {
            $config = array_merge(config('upbond'), [
                'domain' =>  $domain,
                'client_id' => $client,
                'client_secret' => (new LaravelSessionStore)->get('secret')
            ]);
            $service = new AuthService($config);
        }else{
        
            // Get a handle of the Auth service (we don't know if it has an alias)
            $service = \App::make('upbond');

        }

        // Try to get the user information
        $profile = $service->getUser();
        
        // Get the user related to the profile
        $upbondUser = $this->userRepository->getUserByUserInfo($profile);
        
        if ($upbondUser) {
            // If we have a user, we are going to log them in, but if
            // there is an onLogin defined we need to allow the Laravel developer
            // to implement the user as they want an also let them store it.
            if ($service->hasOnLogin()) {
                $user = $service->callOnLogin($upbondUser);
            } else {
                // If not, the user will be fine
                $user = $upbondUser;
            }
            \Auth::login($user, $service->rememberUser());
        }

        return \Redirect::intended('/');
    }
}
