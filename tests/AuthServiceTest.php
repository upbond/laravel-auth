<?php
namespace Upbond\Auth\Login\Tests;

use Upbond\Auth\Login\AuthJWTUser;
use Upbond\Auth\Login\AuthService;
use Upbond\Auth\Login\Facade\Auth as AuthFacade;
use Upbond\Auth\Login\LoginServiceProvider as AuthServiceProvider;
use Upbond\Auth\SDK\Exception\InvalidTokenException;
use Upbond\Auth\SDK\Store\SessionStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class AuthServiceTest extends OrchestraTestCase
{
    public static $defaultConfig;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$defaultConfig = [
            'domain' => 'test.upbond.com',
            'client_id' => '__test_client_id__',
            'client_secret' => '__test_client_secret__',
            'redirect_uri' => 'https://example.com/callback',
            'transient_store' => new SessionStore(),
            'api_identifier' => 'https://example-audience.com'
        ];
    }

    public function tearDown() : void
    {
        Cache::flush();
    }

    public function testThatServiceUsesSessionStoreByDefault()
    {
        session(['upbond__user' => '__test_user__']);
        $service = new AuthService(self::$defaultConfig);
        $user = $service->getUser();

        $this->assertArrayHasKey('profile', $user);
        $this->assertEquals('__test_user__', $user['profile']);
    }

    public function testThatServiceSetsEmptyStoreFromConfigAndConstructor()
    {
        session(['upbond__user' => '__test_user__']);

        $service = new AuthService(self::$defaultConfig + ['store' => false]);
        $this->assertNull($service->getUser());

        $service = new AuthService(self::$defaultConfig);
        $this->assertIsArray($service->getUser());
    }

    public function testThatServiceLoginReturnsRedirect()
    {
        $service = new AuthService(self::$defaultConfig);
        $redirect = $service->login();

        $this->assertInstanceOf( RedirectResponse::class, $redirect );

        $targetUrl = parse_url($redirect->getTargetUrl());

        $this->assertEquals('test.upbond.com', $targetUrl['host']);

        $targetUrlQuery = explode('&', $targetUrl['query']);

        $this->assertContains('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $targetUrlQuery);
        $this->assertContains('client_id=__test_client_id__', $targetUrlQuery);
    }

    /**
     * @throws InvalidTokenException
     */
    public function testThatServiceCanUseLaravelCache()
    {
        $cache_key = md5('https://__invalid_domain__/.well-known/jwks.json');
        cache([$cache_key => [uniqid()]], 10);
        session(['upbond__nonce' => uniqid()]);

        $service = new AuthService(['domain' => '__invalid_domain__'] + self::$defaultConfig);

        // Without the cache set above, would expect a cURL error for a bad domain.
        $this->expectException(InvalidTokenException::class);
        $service->decodeJWT(uniqid());
    }

    public function testThatGuardAuthenticatesUsers()
    {
        $this->assertTrue(\Auth('upbond')->guest());

        $user = new AuthJWTUser(['sub' => 'x']);

        \Auth('upbond')->setUser($user);

        $this->assertTrue(\Auth('upbond')->check());
    }

    /*
     * Test suite helpers
     */

    protected function getPackageProviders($app)
    {
        return [AuthServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Auth' => AuthFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.guards.upbond', ['driver' => 'upbond', 'provider' => 'upbond']);
        $app['config']->set('auth.providers.upbond', ['driver' => 'upbond']);
        $app['config']->set('upbond', self::$defaultConfig);
    }
}
