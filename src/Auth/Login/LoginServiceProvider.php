<?php

namespace Upbond\Auth\Login;

use Upbond\Auth\Login\Contract\AuthUserRepository as AuthUserRepositoryContract;
use Upbond\Auth\Login\Repository\AuthUserRepository;
use Upbond\Auth\SDK\API\Helpers\ApiClient;
use Upbond\Auth\SDK\API\Helpers\InformationHeaders;
use Upbond\Auth\SDK\Store\StoreInterface;
use Illuminate\Auth\RequestGuard;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class LoginServiceProvider extends ServiceProvider
{

    const SDK_VERSION = "6.0.1";

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        \Auth::provider('auth0', function ($app, array $config) {
            return $app->make(AuthUserProvider::class);
        });

        \Auth::extend('auth0', function ($app, $name, $config) {
            return new RequestGuard(function (Request $request, AuthUserProvider $provider) {
                return $provider->retrieveByCredentials(['api_token' => $request->bearerToken()]);
            }, $app['request'], $app['auth']->createUserProvider($config['provider']));
        });

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('laravel-auth0.php'),
        ]);

        $laravel = app();

        $oldInfoHeaders = ApiClient::getInfoHeadersData();

        if ($oldInfoHeaders) {
            $infoHeaders = InformationHeaders::Extend($oldInfoHeaders);

            $infoHeaders->setEnvProperty('Laravel', $laravel::VERSION);
            $infoHeaders->setPackage('laravel-auth0', self::SDK_VERSION);

            ApiClient::setInfoHeadersData($infoHeaders);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(StoreInterface::class, function () {
            return new LaravelSessionStore();
        });

        $this->app->bind(AuthUserRepositoryContract::class, AuthUserRepository::class);

        // Bind the auth0 name to a singleton instance of the Auth Service
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make('config')->get('laravel-auth0'),
                $app->make(StoreInterface::class),
                $app->make('cache.store')
            );
        });
        $this->app->singleton('auth0', function () {
            return $this->app->make(AuthService::class);
        });

        // When Laravel logs out, logout the auth0 SDK trough the service
        \Event::listen('auth.logout', function () {
            \App::make('auth0')->logout();
        });
        \Event::listen('user.logout', function () {
            \App::make('auth0')->logout();
        });
        \Event::listen('Illuminate\Auth\Events\Logout', function () {
            \App::make('auth0')->logout();
        });
    }
}
