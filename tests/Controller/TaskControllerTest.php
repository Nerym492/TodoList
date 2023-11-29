<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $adminUser;
    private User $basicUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $entityManager = $this->client->getContainer()->get('doctrine');
        $this->adminUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'Admin1234']);
        $this->basicUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'Test1234']);
    }

    public function testTaskPageIsUp(): void
    {
        $this->client->request('GET', '/tasks');
        self::assertResponseStatusCodeSame(200);
    }
}
