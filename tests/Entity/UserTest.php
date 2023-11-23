<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testCanGetAndSetData(): void
    {
        $user = new User(
            username: 'Test1234',
            roles: 'ROLE_USER',
            password: '$2y$10$7bjpLtPI8/3.lbjJKklFnexSSP8s4cwyBVzzSXoT0Y70DcmJ5pxZ2',
            email: 'testuser@gmail.com'
        );

        self::assertNull(
            $user->getId(),
            'User id must be null'
        );
        self::assertSame(
            'Test1234',
            $user->getUsername(),
            'Usernames are not identical'
        );
        self::assertSame(
            'testuser@gmail.com',
            $user->getEmail(),
            'Emails are not identical'
        );
        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles(),
            'Roles are not identical'
        );
        self::assertSame(
            'ROLE_USER',
            $user->getRolesAsString(),
            'Roles is not returned as a string'
        );
        self::assertSame(
            '$2y$10$7bjpLtPI8/3.lbjJKklFnexSSP8s4cwyBVzzSXoT0Y70DcmJ5pxZ2',
            $user->getPassword(),
            'Passwords are not identical'
        );
        self::assertSame(
            'Test1234',
            $user->getUserIdentifier(),
            'The user identifier must be identical to the username'
        );
    }

    public function testGetTasks(): void
    {
        $user = new User();
        $tasks = $user->getTasks();

        self::assertInstanceOf(
            Collection::class,
            $tasks,
            'The method getTasks must return a instance of doctrine collection'
        );
        self::assertCount(
            0,
            $tasks,
            'The collection must be empty when a user is initialized'
        );
    }

    public function testAddTasks(): void
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);
        $tasks = $user->getTasks();

        self::assertCount(
            1,
            $tasks,
            'No task has been added'
        );
        self::assertSame(
            $user,
            $task->getUser(),
            'The task is not linked to the user'
        );
    }

    public function testRemoveTasks(): void
    {
        $user = new User();
        $task = new Task();

        $user->addTask($task);
        $user->removeTask($task);

        $tasks = $user->getTasks();

        self::assertCount(
            0,
            $tasks,
            'The task has not been deleted'
        );
        self::assertNull(
            $task->getUser(),
            'The task is still linked to the user'
        );
    }
}
