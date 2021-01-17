<?php

namespace Upbond\Auth\Login;

use Exception;
use Upbond\Auth\SDK\API\Header\AuthorizationBearer;
use Upbond\Auth\SDK\API\Helpers\ApiClient;

/**
 * This class represents a generic user initialized with the user information
 * given by Auth and provides a way to access to the user profile.
 */
abstract class BaseAuthUser implements \Illuminate\Contracts\Auth\Authenticatable
{
    public $apiClient;

    public $eventClient;

    public function __construct(string $accessToken)
    {
        $this->apiClient = new ApiClient([
            'domain' => 'https://'.config('upbond.domain'),
            'basePath' => '/authenticate/',
            // 'guzzleOptions' => $guzzleOptions,
            // 'returnType' => 'object',
            'headers' => [
                new AuthorizationBearer($accessToken)
            ]
        ]);
    }

    public function update(array $data = [])
    {

        $response =  $this->apiClient->method('post')
        ->addPath('user')
        ->withBody(json_encode($data))
        ->call();

        if ($response) {
            $this->userInfo = array_merge($this->userInfo, $data);
        }else{
            throw new Exception('customer update fail');
        }
    }

    public function eventPublish(array $data = [])
    {
        $response =  $this->apiClient->method('post')
        ->addPath('event')
        ->withBody(json_encode($data))
        ->call();
    }
}