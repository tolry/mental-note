<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Criteria\EntryCriteria;
use App\Entity\Category;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultControllerTest extends WebTestCase
{
    public function testLoginFirewall(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertRedirect($client->getResponse(), 'http://localhost/login');
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

        $urlWithReducedPageCount = '/?' . http_build_query(['filter' => ['page' => 999998]]);
        $this->assertRedirect($client->getResponse(), $urlWithReducedPageCount);
    }

    public function testIndexWithFilter(): void
    {
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['filter' => ['tag' => 'CSS', 'category' => Category::READ, 'sort' => EntryCriteria::SORT_TIMESTAMP_DESC, 'query' => 'foo']]);
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
