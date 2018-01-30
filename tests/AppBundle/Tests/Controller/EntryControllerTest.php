<?php

declare(strict_types = 1);

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Category;
use AppBundle\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
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

    public function testThumbnail(): void
    {
        $client = $this->getAuthenticatedClient();
        $id = $this->createNew($client);

        $imageLink = "/thumbnails/${id}_100x100.png";

        $client->request('GET', $imageLink);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($imageLink, $response->getTargetUrl());
    }

    public function testTogglePending(): void
    {
        $client = $this->getAuthenticatedClient();
        $id = $this->createNew($client);

        $crawler = $client->request('POST', "/entry/$id/toggle_pending.json");
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());

        $crawler = $client->request('POST', "/entry/$id/toggle_pending.json");
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    private function createNew(Client $client): int
    {
        $crawler = $client->request('GET', '/entry/create.html');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name="entry"]')->count());

        $uniqueUrl = 'https://www.tobias-olry.de/?mental-note-functional-test=' . uniqid();

        $form = $crawler->filter('form[name="entry"]')->form();
        $form['entry[title]'] = 'functional test add entry';
        $form['entry[url]'] = $uniqueUrl;
        $form['entry[category]'] = Category::READ;
        $form['entry[tags]'] = null;

        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());

        $crawler = $client->request('GET', '/');
        $visitLink = $crawler->filter(".card>a[href='$uniqueUrl']")->first()->attr('data-link');
        if (!preg_match('%/entry/(\d+)/visit%', $visitLink, $matches)) {
            $this->fail("entry creation failed for $uniqueUrl, could not find ID in $visitLink");
        }

        return (int) $matches[1];
    }
}
