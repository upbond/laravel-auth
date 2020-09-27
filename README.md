# Laravel Upbond Auth Plugin

This package is inspired by Auth0

## Documentation

Please see the [Laravel webapp quickstart](https://auth0.com/docs/quickstart/webapp/laravel) for a complete guide on how to install this in an existing project or to download a pre-configured sample project. Additional documentation on specific scenarios is below.

### Setting up

```
$this->app->bind(
    \Upbond\Auth\Login\Contract\AuthUserRepository::class,
    \Upbond\Auth\Login\Repository\AuthUserRepository::class
);
```

### Setting up a JWKs cache

In the `register` method of your `AppServiceProvider` add:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Cache;
// ...
    public function register()
    {
        // ...
        $this->app->bind(
            '\Auth\SDK\Helpers\Cache\CacheHandler',
            function() {
                static $cacheWrapper = null;
                if ($cacheWrapper === null) {
                $cache = Cache::store();
                $cacheWrapper = new LaravelCacheWrapper($cache);
            }
            return $cacheWrapper;
        });
    }
```

### Setting up for lumen
```
$app->register(Upbond\Auth\Login\LoginServiceProvider::class);
```

### Update .env for your env default to api.upbond.io
```
UPBOND_API_URI=api.dev.upbond.io
```

You can implement your own cache strategy by creating a new class that implements the `Auth\SDK\Helpers\Cache\CacheHandler` contract, or just use the cache strategy you want by picking that store with `Cache::store('your_store_name')`;

### Storing users in your database

You can customize the way you handle the users in your application by creating your own `UserRepository`. This class should implement the `Auth\Login\Contract\AuthUserRepository` contract. Please see the [Custom User Handling section of the Laravel Quickstart](https://auth0.com/docs/quickstart/webapp/laravel#optional-custom-user-handling) for the latest example.

### Using auth guard

To protect APIs using an access token generated by Auth, there is an `upbond` API guard provided ([Laravel documentation on guards](https://laravel.com/docs/7.x/authentication#adding-custom-guards)). To use this guard, add it to `config/auth.php` with the driver `upbond`:
```


'providers' => [
    
    'users' => [
        'driver' => 'upbond',
    ],
],
```

Once that has been added, add the guard to the middleware of any API route and check authentication during the request:
```
// get user
auth('upbond')->user();
// check if logged in
auth('upbond')->check();
// protect routes via middleware use
Route::group(['middleware' => 'auth:upbond'], function () {});

Route::get( '/auth/callback', '\Upbond\Auth\Login\AuthController@callback' )->name( 'auth-callback' );

```

## env file

```
UPBOND_API_URI
UPBOND_AUTH_DOMAIN
UPBOND_AUTH_CLIENT_ID
UPBOND_AUTH_CLIENT_SECRET
```


## Installation

Install this plugin into a new or existing project using [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
$ composer require upbond/laravel-auth
```

Additional steps to install can be found in the [quickstart](https://auth0.com/docs/quickstart/webapp/laravel#integrate-auth0-in-your-application).

## Contributing

We appreciate feedback and contribution to this repo! Before you get started, please see the following:

- [Auth's Contribution guidelines](https://github.com/auth0/.github/blob/master/CONTRIBUTING.md)
- [Auth's Code of Conduct](https://github.com/auth0/open-source-template/blob/master/CODE-OF-CONDUCT.md)

## Support + Feedback

Include information on how to get support. Consider adding:

- Use [Community](https://community.auth0.com/tags/laravel) for usage, questions, specific cases
- Use [Issues](https://github.com/auth0/upbond-auth/issues) for code-level support

## What is Auth?

Auth helps you to easily:

- implement authentication with multiple identity providers, including social (e.g., Google, Facebook, Microsoft, LinkedIn, GitHub, Twitter, etc), or enterprise (e.g., Windows Azure AD, Google Apps, Active Directory, ADFS, SAML, etc.)
- log in users with username/password databases, passwordless, or multi-factor authentication
- link multiple user accounts together
- generate signed JSON Web Tokens to authorize your API calls and flow the user identity securely
- access demographics and analytics detailing how, when, and where users are logging in
- enrich user profiles from other data sources using customizable JavaScript rules

[Why Auth?](https://auth0.com/why-auth0)

## License

The Auth Laravel Login plugin is licensed under MIT - [LICENSE](LICENSE.txt)
