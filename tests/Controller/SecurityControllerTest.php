<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $basicUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $entityManager = $this->client->getContainer()->get('doctrine');
        $this->basicUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'Test1234']);
    }

    public function testUserCanLogIn(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'Test1234',
            '_password' => 'Test1234*',
        ]);
        $this->client->submit($form);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('h1', 'Bienvenue');
    }

    public function testCantAccessLoginPageIfAuthenticated(): void
    {
        $this->client->loginUser($this->basicUser);
        $this->client->request('GET', '/login');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('h1', 'Bienvenue');
    }

    public function testUserCanLogOut(): void
    {
        $this->client->loginUser($this->basicUser);

        $this->client->request('GET', '/logout');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('button', 'Se connecter');
    }
}
