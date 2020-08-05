<?php

namespace Upbond\Auth\Login\Tests\Unit;

use Upbond\Auth\Login\AuthJWTUser;
use PHPUnit\Framework\TestCase;

class AuthJWTUserTest extends TestCase
{
    /**
     * @var AuthJWTUser
     */
    protected $auth0JwtUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->auth0JwtUser = new AuthJWTUser([
            "name" => "John Doe",
            "iss" => "http://auth0.com",
            "sub" => "someone@example.com",
            "aud" => "http://example.com",
            "exp" => 1357000000
        ]);
    }

    public function testAuthIdentifierNameIsSubjectOfJWTToken()
    {
        $this->assertEquals('someone@example.com', $this->auth0JwtUser->getAuthIdentifierName());
    }

    public function testAuthIdentifierIsSubjectOfJWTToken()
    {
        $this->assertEquals('someone@example.com', $this->auth0JwtUser->getAuthIdentifier());
    }

    public function testGetAuthPasswordWillNotReturnAnything()
    {
        $this->assertEquals('', $this->auth0JwtUser->getAuthPassword());
    }

    public function testObjectHoldsNoRememberTokenInformation()
    {
        $this->auth0JwtUser->setRememberToken('testing123');

        $this->assertEquals('', $this->auth0JwtUser->getRememberToken());
        $this->assertEquals('', $this->auth0JwtUser->getRememberTokenName());
    }

    public function testGettersCanReturnTokenClaims()
    {
        // Retrieve issuer claim
        $this->assertEquals('http://auth0.com', $this->auth0JwtUser->iss);
    }
}
