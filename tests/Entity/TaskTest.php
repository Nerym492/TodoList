<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    public function setUp(): void
    {
        $kernel = self::bootKernel();
    }

    public function testCanGetAndSetTaskData(): void
    {
        $creationDate = new \DateTime('2023-11-23 14:30:00');
        $task = new Task(
            createdAt: $creationDate,
            title: 'This is a new task',
            content: 'Some content for the task',
            isDone: true
        );

        self::assertNull(
            $task->getId(),
            'Task id must be null'
        );
        self::assertSame(
            $creationDate,
            $task->getCreatedAt(),
            'Creation dates are not identical.'
        );
        self::assertSame(
            'This is a new task',
            $task->getTitle(),
            'Titles are not identical.'
        );
        self::assertSame(
            'Some content for the task',
            $task->getContent(),
            'Contents are not identical.'
        );
        self::assertSame(
            true,
            $task->isDone(),
            'isDone types/values are not identical'
        );
    }

    public function testGetUser(): void
    {
        $task = new Task();
        $user = $task->getUser();

        self::assertNull(
            $user,
            'User must be set to null by default'
        );
    }

    public function testSetUser(): void
    {
        $task = new Task();
        $user = new User();

        $task->setUser($user);

        self::assertInstanceOf(
            User::class,
            $task->getUser(),
            'Task user is not an instance of \App\Entity\User'
        );
    }
}
