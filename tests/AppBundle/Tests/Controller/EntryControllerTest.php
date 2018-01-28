<?php

declare(strict_types = 1);

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Category;
use AppBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntryControllerTest extends WebTestCase
{
    public function testCreate(): void
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', '/entry/create.html');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name="entry"]')->count());

        $form = $crawler->filter('form[name="entry"]')->form();
        $form['entry[title]'] = 'functional test add entry';
        $form['entry[url]'] = 'https://www.tobias-olry.de/?mental-note-functional-test=' . uniqid();
        $form['entry[category]'] = Category::READ;
        $form['entry[tags]'] = 'one, two, three';

        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function testCreateEmptyTags(): void
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', '/entry/create.html');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name="entry"]')->count());

        $form = $crawler->filter('form[name="entry"]')->form();
        $form['entry[title]'] = 'functional test add entry';
        $form['entry[url]'] = 'https://www.tobias-olry.de/?mental-note-functional-test=' . uniqid();
        $form['entry[category]'] = Category::READ;
        $form['entry[tags]'] = null;

        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function testIndexWithFilter(): void
    {
        $this->markTestSkipped();
        $client = $this->getAuthenticatedClient();

        $query = http_build_query(['filter' => ['tag' => 'CSS']]);
        $crawler = $client->request('GET', "/?$query");
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('.user-dropdown')->count());
    }
}
