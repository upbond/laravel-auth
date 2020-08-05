<?php

namespace Upbond\Auth\Login\Tests\Unit;

use Upbond\Auth\Login\AuthJWTUser;
use PHPUnit\Framework\TestCase;

class AuthJWTUserTest extends TestCase
{
    /**
     * @var AuthJWTUser
     */
    protected $upbondJwtUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->upbondJwtUser = new AuthJWTUser([
            "name" => "John Doe",
            "iss" => "http://upbond.com",
            "sub" => "someone@example.com",
            "aud" => "http://example.com",
            "exp" => 1357000000
        ]);
    }

    public function testAuthIdentifierNameIsSubjectOfJWTToken()
    {
        $this->assertEquals('someone@example.com', $this->upbondJwtUser->getAuthIdentifierName());
    }

    public function testAuthIdentifierIsSubjectOfJWTToken()
    {
        $this->assertEquals('someone@example.com', $this->upbondJwtUser->getAuthIdentifier());
    }

    public function testGetAuthPasswordWillNotReturnAnything()
    {
        $this->assertEquals('', $this->upbondJwtUser->getAuthPassword());
    }

    public function testObjectHoldsNoRememberTokenInformation()
    {
        $this->upbondJwtUser->setRememberToken('testing123');

        $this->assertEquals('', $this->upbondJwtUser->getRememberToken());
        $this->assertEquals('', $this->upbondJwtUser->getRememberTokenName());
    }

    public function testGettersCanReturnTokenClaims()
    {
        // Retrieve issuer claim
        $this->assertEquals('http://upbond.com', $this->upbondJwtUser->iss);
    }
}
