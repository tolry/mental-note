<?php

declare(strict_types = 1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends BaseWebTestCase
{
    protected const TEST_USER_USERNAME = 'tests';
    protected const TEST_USER_PASSWORD = 'tests-password';

    protected function getAuthenticatedClient(): Client
    {
        return static::createClient(
            [],
            [
                'PHP_AUTH_USER' => static::TEST_USER_USERNAME,
                'PHP_AUTH_PW' => static::TEST_USER_PASSWORD,
            ]
        );
    }

    protected function assertRedirect(Response $response, string $location)
    {
        $this->assertTrue($response->isRedirect(), 'Response is not a redirect, got status code: ' . $response->getStatusCode());
        $this->assertEquals($location, $response->headers->get('Location'));
    }
}
