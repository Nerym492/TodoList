<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    #[Route(path: 'tasks', name: 'task_list', methods: ['GET'])]
    public function listAction(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        $tasksInfos = [];

        foreach ($tasks as $task) {
            $taskId = $task->getId();
            $tasksInfos[$taskId]['taskCreator'] = 'Anonyme';
            $tasksInfos[$taskId]['allowDelete'] = false;
            $taskCreator = $task->getUser();
            $loggedUser = $this->security->getUser();
            $tasksInfos[$taskId]['isTaskCreator'] = false;

            // Checks if the logged-in user is the creator of this task
            if ($taskCreator && $loggedUser && $taskCreator->getUsername() === $loggedUser->getUserIdentifier()) {
                $tasksInfos[$taskId]['isTaskCreator'] = true;
            }

            /* The task creator is anonymous and the logged-in user is an admin
             * OR the task belong to the logged-in user */
            if ($this->security->isGranted('ROLE_ADMIN') && !$taskCreator
                || $tasksInfos[$taskId]['isTaskCreator']
            ) {
                $tasksInfos[$taskId]['allowDelete'] = true;
            }
        }

        return $this->render(
            'task/list.html.twig',
            [
                'tasks' => $tasks,
                'tasksInfos' => $tasksInfos,
            ]
        );
    }

    #[Route(path: '/tasks/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function createAction(Request $request, UserRepository $userRepository): RedirectResponse|Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $loggedUser = $this->security->getUser();
            if ($loggedUser) {
                $username = $loggedUser->getUserIdentifier();
                $user = $userRepository->findOneBy(['username' => $username]);
                $task->setUser($user);
            }

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function editAction(Task $task, Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle', methods: ['PUT'])]
    public function toggleTaskAction(Task $task): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTaskAction(Task $task): RedirectResponse
    {
        $taskUser = $task->getUser();
        $connectedUser = $this->security->getUser();
        $allowDelete = false;

        if ($connectedUser) {
            // Only the administrator can delete an anonymous task
            $allowDelete = !$taskUser && 'ROLE_ADMIN' === implode(',', $connectedUser->getRoles());
        }

        if ($taskUser && $connectedUser) {
            // Only the user who created the task can delete it
            $allowDelete = $taskUser->getUsername() === $connectedUser->getUserIdentifier();
        }

        if (!$allowDelete) {
            $this->addFlash('error', "Vous n'êtes pas autorisé à supprimer cette tâche.");

            return $this->redirectToRoute('task_list');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
