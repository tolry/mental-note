<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserControllerTest extends WebTestCase
{
    public function testChangePassword(): void
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', '/user/change-password');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name="fos_user_change_password_form"]')->count());

        $form = $crawler->filter('form[name="fos_user_change_password_form"]')->form();
        $form['fos_user_change_password_form[current_password]'] = static::TEST_USER_PASSWORD;
        $form['fos_user_change_password_form[plainPassword][first]'] = 'test-password-change';
        $form['fos_user_change_password_form[plainPassword][second]'] = 'test-password-change';
        $crawler = $client->submit($form);

        $this->assertRedirect($client->getResponse(), '/');

        //
        // revert password to original
        //

        $crawler = $client->request('GET', '/user/change-password');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name="fos_user_change_password_form"]')->count());

        $form = $crawler->filter('form[name="fos_user_change_password_form"]')->form();
        $form['fos_user_change_password_form[current_password]'] = 'test-password-change';
        $form['fos_user_change_password_form[plainPassword][first]'] = static::TEST_USER_PASSWORD;
        $form['fos_user_change_password_form[plainPassword][second]'] = static::TEST_USER_PASSWORD;
        $crawler = $client->submit($form);

        $this->assertRedirect($client->getResponse(), '/');
    }
}
