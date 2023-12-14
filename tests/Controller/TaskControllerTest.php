<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

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

        $crawler->filter('.task')->each(function (Crawler $node) {
            $deleteBtnNode = $node->filter('.btn-danger');
            if (1 === $deleteBtnNode->count()) {
                self::assertTrue(
                    $node->filter('.task-creator')->text() === 'Créé par '.$this->basicUser->getUsername(),
                    'The user must not be able to see the delete button if he is not the task creator.'
                );
            }
        });
    }

    public function testConnectedUserCanCreateTask(): void
    {
        $this->checkTaskFormSubmission(
            uri: '/tasks/create',
            buttonValue: 'Ajouter',
            errorMessage: 'A connected user must be able to create a task',
            flashMessage: 'La tâche a été bien été ajoutée.',
            user: $this->basicUser
        );
    }

    public function testAnonymousUserCanCreateTask(): void
    {
        $this->checkTaskFormSubmission(
            uri: '/tasks/create',
            buttonValue: 'Ajouter',
            errorMessage: 'An anonymous user must be able to create a task',
            flashMessage: 'La tâche a été bien été ajoutée.'
        );
    }

    public function testTaskCanBeModified(): void
    {
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Another Task']);

        $this->checkTaskFormSubmission(
            uri: '/tasks/'.$task->getId().'/edit',
            buttonValue: 'Modifier',
            errorMessage: 'A task must be editable',
            flashMessage: 'La tâche a bien été modifiée.'
        );
    }

    public function testAnonymousUserCannotDeleteTask(): void
    {
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Another Task']);

        $this->client->request('DELETE', 'tasks/'.$task->getId().'/delete');

        self::assertTrue(
            '/tasks' === parse_url($this->client->getCrawler()->getUri(), PHP_URL_PATH),
            'The user must be redirected to the task list'
        );
        self::assertSelectorTextContains(
            'body',
            "Vous n'êtes pas autorisé à supprimer cette tâche.",
            'An anonymous user must not be able to delete a task'
        );
    }

    public function testConnectedUserCanOnlyDeleteHisOwnTasks(): void
    {
        $this->client->loginUser($this->basicUser);
        $taskRepository = $this->entityManager->getRepository(Task::class);

        // Connected user is the creator of this task
        $connectedUserTask = $taskRepository->findOneBy(['user' => $this->basicUser->getId()]);
        // This task has been created by another user
        $taskNotCreatedByUser = $taskRepository->findOneWithDifferentUser($this->basicUser);

        $this->checkTaskDeletion(
            task: $connectedUserTask,
            flashMessage: 'La tâche a bien été supprimée.'
        );
        $this->checkTaskDeletion(
            task: $taskNotCreatedByUser,
            flashMessage: "Vous n'êtes pas autorisé à supprimer cette tâche."
        );
    }

    public function testOnlyAdminCanDeleteAnonymousTask(): void
    {
        $this->client->loginUser($this->basicUser);
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['user' => null]);

        $this->checkTaskDeletion(
            task: $task,
            flashMessage: "Vous n'êtes pas autorisé à supprimer cette tâche."
        );

        $this->client->loginUser($this->adminUser);
        $this->checkTaskDeletion(
            task: $task,
            flashMessage: 'La tâche a bien été supprimée.'
        );
    }

    public function testTaskStatusCanBeChanged(): void
    {
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Test task']);

        $this->client->request('PUT', 'tasks/'.$task->getId().'/toggle');

        self::assertTrue(
            '/tasks' === parse_url($this->client->getCrawler()->getUri(), PHP_URL_PATH),
            'The user must be redirected to the task list'
        );
        self::assertSelectorTextContains(
            'body',
            sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle())
        );
    }

    private function checkTaskDeletion(Task $task, string $flashMessage): void
    {
        $this->client->request('DELETE', '/tasks/'.$task->getId().'/delete');
        self::assertTrue(
            '/tasks' === parse_url($this->client->getCrawler()->getUri(), PHP_URL_PATH),
            'The user must be redirected to the task list'
        );
        self::assertSelectorTextContains('body', $flashMessage);
    }

    private function checkTaskFormSubmission(string $uri, string $buttonValue, string $errorMessage,
        string $flashMessage, User $user = null): void
    {
        if ($user) {
            $this->client->loginUser($user);
        }

        $crawler = $this->client->request('GET', $uri);
        $form = $crawler->selectButton($buttonValue)->form([
            'task[title]' => 'Test task 1234',
            'task[content]' => 'Some content for the test task 1234.',
        ]);
        $this->client->submit($form);

        self::assertTrue(
            '/tasks' === parse_url($this->client->getCrawler()->getUri(), PHP_URL_PATH),
            $errorMessage
        );

        self::assertSelectorTextContains('body', $flashMessage);
    }
}
