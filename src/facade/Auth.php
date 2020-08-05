<?php

namespace Upbond\Auth\Login\Facade;

class Auth extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth0';
    }
}
