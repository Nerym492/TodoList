<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?object $entityManager;
    private User $adminUser;
    private User $basicUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $this->entityManager = $this->client->getContainer()->get('doctrine');
        $this->adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'Admin1234']);
        $this->basicUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'Test1234']);
    }

    public function testTaskPageIsUp(): void
    {
        $this->client->request('GET', '/tasks');
        self::assertResponseStatusCodeSame(200);
    }

    public function testTaskDeleteButtonVisibleIfUserIsTaskCreator(): void
    {
        $this->client->loginUser($this->basicUser);
        $crawler = $this->client->request('GET', '/tasks');

        $crawler->filter('.task')->each(function ($node) {
            $deleteBtnNode = $node->filter('.btn-danger');
            if (1 === $deleteBtnNode->count()) {
                self::assertTrue(
                    $node->filter('.task-creator')->text() === 'Créé par '.$this->basicUser->getUsername(),
                    'The user must not be able to see the delete button if he is not the task creator.'
                );
            }
        });
    }
}
