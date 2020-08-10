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
use Upbond\Auth\SDK\Store\EmptyStore;

class LoginServiceProvider extends ServiceProvider
{

    const SDK_VERSION = "1.0.1";

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        \Auth::provider('upbond', function ($app, array $config) {
            return $app->make(AuthUserProvider::class);
        });

        \Auth::extend('upbond', function ($app, $name, $config) {
            return new RequestGuard(function (Request $request, AuthUserProvider $provider) {
                $user = $provider->retrieveByCredentials(['api_token' => $request->bearerToken()]);
                if ($user) $request->merge(['account' => $user->account]);
                return $user;
            }, $app['request'], $app['auth']->createUserProvider($config['provider']));
        });

        $this->publishes([
            __DIR__.'/../config/config.php' => base_path('config/upbond.php'),
        ]);

        $this->bootAccountAuth();

        $oldInfoHeaders = ApiClient::getInfoHeadersData();

        if ($oldInfoHeaders) {
            $infoHeaders = InformationHeaders::Extend($oldInfoHeaders);

            // $infoHeaders->setEnvProperty('Laravel', $laravel::VERSION);
            $infoHeaders->setPackage('upbond', self::SDK_VERSION);

            ApiClient::setInfoHeadersData($infoHeaders);
        }
    }
    /**
     * Upbond Account service - replaced with upbond auth in the future
     */

    private function bootAccountAuth()
    {

        $this->app['auth']->viaRequest('admin', function ($request) {
            $client = new \GuzzleHttp\Client([
                'base_uri' => env('AUTH_URL', 'http://account-service:8080'),
              ]);

              try {
                $res = $client->post("oauth/token/validate",[
                    'headers' => [
                        'authorization' =>  $request->header('authorization'),
                    ]
                ]);
    
            } catch (Exception $e) {
               return response('Unauthorized.', 401);
            }
            $data = json_decode($res->getBody(), true);
            $account = $data[0];
            $user =  new GenericUser($account);

            $request->merge([
                'account' => $user->id
            ]);
            return $user;
        });

    }


    /**
     * Register the service provider.
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/config.php';
        $this->mergeConfigFrom($configPath, 'upbond');

        if (app() instanceof \Illuminate\Foundation\Application) {
            $this->app->bind(StoreInterface::class, function () {
                return new LaravelSessionStore();
            });
        } else {
            $this->app->bind(StoreInterface::class, function () {
                return new EmptyStore();
            });
        }



        $this->app->bind(AuthUserRepositoryContract::class, AuthUserRepository::class);
        
        // Bind the upbond name to a singleton instance of the Auth Service
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make('config')->get('upbond'),
                $app->make(StoreInterface::class),
                $app->make('cache.store')
            );
        });
        $this->app->singleton('upbond', function () {
            return $this->app->make(AuthService::class);
        });

        // When Laravel logs out, logout the upbond SDK trough the service
        \Event::listen('auth.logout', function () {
            \App::make('upbond')->logout();
        });
        \Event::listen('user.logout', function () {
            \App::make('upbond')->logout();
        });
        \Event::listen('Illuminate\Auth\Events\Logout', function () {
            \App::make('upbond')->logout();
        });
    }
}
