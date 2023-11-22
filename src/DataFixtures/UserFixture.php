<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setRoles('ROLE_USER');
        $user->setEmail('testuser@gmail.com');
        $user->setUsername('Test1234');
        // Password : Test1234*
        $user->setPassword('$2y$10$7bjpLtPI8/3.lbjJKklFnexSSP8s4cwyBVzzSXoT0Y70DcmJ5pxZ2');

        $manager->persist($user);

        $manager->flush();
    }
}
