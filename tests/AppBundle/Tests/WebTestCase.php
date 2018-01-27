<?php

declare(strict_types = 1);

namespace AppBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class WebTestCase extends BaseWebTestCase
{
    protected function getAuthenticatedClient(): Client
    {
        return static::createClient(
            [],
            ['PHP_AUTH_USER' => 'tests', 'PHP_AUTH_PW' => 'tests-password',]
        );
    }
}
