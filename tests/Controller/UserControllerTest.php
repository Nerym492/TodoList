<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    public function testAccessRestrictedOnUserPages(): void
    {
        $userPages = [
            'user_list' => '/users',
            'user_create' => '/users/create',
            'user_edit' => '/users/'.$this->basicUser->getId().'/edit',
        ];
        $accessResponse = [
            'ROLE_ADMIN' => ['responseCode' => 200, 'message' => ' must have access to the page '],
            'ROLE_USER' => ['responseCode' => 403, 'message' => ' must not have access to the page '],
        ];

        $this->checkAccessForUser($this->adminUser, $userPages, $accessResponse);
        $this->checkAccessForUser($this->basicUser, $userPages, $accessResponse);
    }

    public function testAdminCanCreateUser(): void
    {
        $this->checkUserFormSubmission(
            '/users/create',
            'Ajouter',
            [
                'username' => 'newUser456',
                'firstPassword' => 'password',
                'secondPassword' => 'password',
                'email' => 'newUser456@test.com',
                'role' => 'ROLE_USER',
            ],
            'Admin must be able to create a new user',
            "L'utilisateur a bien été ajouté."
        );
    }

    public function testAdminCanEditUser(): void
    {
        $this->checkUserFormSubmission(
            '/users/'.$this->basicUser->getId().'/edit',
            'Modifier',
            [
                'username' => 'modifiedUser456',
                'firstPassword' => 'modifiedPassword',
                'secondPassword' => 'modifiedPassword',
                'email' => 'modifiedUser456@test.com',
                'role' => 'ROLE_ADMIN',
            ],
            'Admin must be able to modify a user',
            "L'utilisateur a bien été modifié"
        );
    }

    private function checkAccessForUser(User $user, array $userPages, array $accessResponse)
    {
        $this->client->loginUser($user);
        // Test access for admin user
        foreach ($userPages as $userPage) {
            $this->client->request('GET', $userPage);
            $userRole = $user->getRolesAsString();
            self::assertSame(
                $accessResponse[$userRole]['responseCode'],
                $this->client->getResponse()->getStatusCode(),
                $userRole.$accessResponse[$userRole]['message'].$userPage
            );
        }
    }

    private function checkUserFormSubmission(string $uri, string $buttonValue, array $userFormValues, string $errorMessage,
        string $successMessage): void
    {
        $this->client->loginUser($this->adminUser);

        $crawler = $this->client->request('GET', $uri);
        $form = $crawler->selectButton($buttonValue)->form([
            'user[username]' => $userFormValues['username'],
            'user[password][first]' => $userFormValues['firstPassword'],
            'user[password][second]' => $userFormValues['secondPassword'],
            'user[email]' => $userFormValues['email'],
            'user[roles]' => $userFormValues['role'],
        ]);
        $this->client->submit($form);

        self::assertTrue(
            '/users' === parse_url($this->client->getCrawler()->getUri(), PHP_URL_PATH),
            $errorMessage
        );
        self::assertSelectorTextContains('body', $successMessage);
    }
}
