<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getDependencies(): array
    {
        return [
          UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $adminUser = $userRepository->findOneBy(['username' => 'Admin1234']);
        $basicUser = $userRepository->findOneBy(['username' => 'Test1234']);

        $this->persistTaskObject(
            'An admin task',
            'This task has been created by an admin',
            new \DateTime('2023-11-24 09:26:14'),
            $adminUser
        );
        $this->persistTaskObject(
            'Test task',
            'Some content for this task',
            new \DateTime('2023-11-24 10:13:43'),
            $basicUser
        );
        $this->persistTaskObject(
            'Another task',
            'This is an anonymous task',
            new \DateTime('2023-11-23 14:30:00')
        );

        $manager->flush();
    }

    public function persistTaskObject(string $title, string $content, \DateTime $createdAt, User $user = null): void
    {
        $task = new Task(
            createdAt: $createdAt,
            title: $title,
            content: $content
        );
        if ($user) {
            $task->setUser($user);
        }
        $this->entityManager->persist($task);
    }
}
