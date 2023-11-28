<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    public function testRedirectionWhenUserIsNotAuthenticated(): void
    {
        $crawler = $this->client->request('GET', '/');

        // Checks if the user is redirected to the login page
        self::assertTrue('/login' === parse_url($crawler->getUri(), PHP_URL_PATH));
        self::assertSelectorTextContains('button', 'Se connecter');
    }

    public function testHomepageWhenUserIsAuthenticated(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine');
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'Test1234']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('h1', 'Bienvenue');
    }
}
