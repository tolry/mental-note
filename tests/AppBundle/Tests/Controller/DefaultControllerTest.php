<?php

declare(strict_types = 1);

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversNothing
 */
class DefaultControllerTest extends WebTestCase
{
    public function testLoginFirewall(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost/login', $response->getTargetUrl());
    }

    public function testIndexSimple(): void
    {
        $client = static::createClient();

        $this->markTestSkipped();

        // @todo

        $this->login();
        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('', $crawler->filter('@todo'));
    }
}
