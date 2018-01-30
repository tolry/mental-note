<?php

declare(strict_types = 1);

namespace AppBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Tests\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testLoginFirewall(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost/login', $response->getTargetUrl());
    }

    public function testIndexSimple(): void
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('.user-dropdown')->count());
    }

    public function testIndexPageOutOfBounds(): void
    {
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['filter' => ['page' => 999999]]);
        $client->request('GET', "/?$query");
        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $queryPageReduced = http_build_query(['filter' => ['page' => 999998]]);
        $this->assertEquals("/?$queryPageReduced", $response->getTargetUrl());
    }

    public function testIndexWithFilter(): void
    {
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['filter' => ['tag' => 'CSS', 'query' => 'foo']]);
        $crawler = $client->request('GET', "/?$query");
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('.user-dropdown')->count());
    }

    public function testUrlMetainfo(): void
    {
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['url' => 'http://www.spiegel.de/']);
        $client->request('GET', "/url/metainfo?$query");
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUrlMetainfoEmptyUrl(): void
    {
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['url' => '']);
        $client->request('GET', "/url/metainfo?$query");
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}
