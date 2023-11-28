<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->persistUserObject(
            role: 'ROLE_USER',
            email: 'testuser@gmail.com',
            username: 'Test1234',
            password: password_hash('Test1234*', PASSWORD_BCRYPT)
        );
        $this->persistUserObject(
            role: 'ROLE_ADMIN',
            email: 'adminuser@gmail.com',
            username: 'Admin1234',
            password: password_hash('Admin1234*', PASSWORD_BCRYPT)
        );

        $manager->flush();
    }

    private function persistUserObject(string $role = '', string $email = null, string $username = null,
        string $password = null): void
    {
        $user = new User();
        $user->setRoles($role);
        $user->setEmail($email);
        $user->setUsername($username);
        // Password : Test1234*
        $user->setPassword($password);

        $this->entityManager->persist($user);
    }
}
