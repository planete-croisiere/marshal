<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\User\Group;
use App\Entity\User\Role;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProtectedPrivilegeTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    /** @var list<User> */
    private array $createdUsers = [];

    protected function setUp(): void
    {
        // Un test précédent peut avoir laissé le noyau démarré
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        try {
            // Nettoyage hors session, comme une commande console
            static::getContainer()->get(TokenStorageInterface::class)->setToken(null);

            foreach ($this->createdUsers as $user) {
                $user = $this->entityManager->find(User::class, $user->getId());
                foreach ($user->getGroups() as $group) {
                    $user->removeGroup($group);
                }
                $this->entityManager->remove($user);
            }
            $this->entityManager->flush();
        } finally {
            parent::tearDown();
        }
    }

    public function testAdministratorCannotEditSuperAdmin(): void
    {
        $this->client->loginUser($this->createUser('administrator@example.com', ['Administrator']));

        $this->client->request('GET', '/admin/user-crud/'.$this->superAdmin()->getId().'/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdministratorIsNotOfferedProtectedGroups(): void
    {
        $this->client->loginUser($this->createUser('administrator@example.com', ['Administrator']));
        $seller = $this->createUser('seller@example.com', ['Commercial']);

        $crawler = $this->client->request('GET', '/admin/user-crud/'.$seller->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $groupOptions = $crawler->filter('select[name="User[groups][]"] option')->each(static fn ($option) => $option->text());
        $this->assertContains('Commercial', $groupOptions);
        $this->assertNotContains(Group::SUPER_ADMIN_NAME, $groupOptions);
        $this->assertNotContains('Technicien', $groupOptions);
    }

    public function testSuperAdminIsOfferedProtectedGroups(): void
    {
        $this->client->loginUser($this->superAdmin());
        $seller = $this->createUser('seller@example.com', ['Commercial']);

        $crawler = $this->client->request('GET', '/admin/user-crud/'.$seller->getId().'/edit');

        $groupOptions = $crawler->filter('select[name="User[groups][]"] option')->each(static fn ($option) => $option->text());
        $this->assertContains(Group::SUPER_ADMIN_NAME, $groupOptions);
    }

    public function testAdministratorCannotToggleProtectedRightsInMatrix(): void
    {
        $this->client->loginUser($this->createUser('administrator@example.com', ['Administrator']));

        $this->toggle('Administrator', 'ROLE_SUPER_ADMIN');
        $this->assertResponseStatusCodeSame(403);

        $this->toggle(Group::SUPER_ADMIN_NAME, 'ROLE_CRM_SELLER');
        $this->assertResponseStatusCodeSame(403);

        $this->entityManager->clear();
        $this->assertFalse($this->group('Administrator')->getRoles()->contains($this->role('ROLE_SUPER_ADMIN')));
    }

    public function testMatrixLocksProtectedRightsForAdministrator(): void
    {
        $this->client->loginUser($this->createUser('administrator@example.com', ['Administrator']));

        // URL du menu de l'administration, qui fournit le contexte EasyAdmin à la page
        $this->client->request('GET', '/admin?routeName=admin_group_role_matrix');

        $this->assertResponseIsSuccessful();
        $props = json_decode(
            (string) $this->client->getCrawler()->filter('[data-symfony--ux-vue--vue-props-value]')->attr('data-symfony--ux-vue--vue-props-value'),
            true,
        );
        $this->assertFalse($props['canGrantProtectedPrivileges']);
        $protectedGroups = array_column(array_filter($props['groups'], static fn (array $group) => $group['protected']), 'name');
        $this->assertEqualsCanonicalizing([Group::SUPER_ADMIN_NAME, 'Technicien'], $protectedGroups);
    }

    public function testAdministratorCanToggleOrdinaryRightsInMatrix(): void
    {
        $this->client->loginUser($this->createUser('administrator@example.com', ['Administrator']));

        $this->toggle('Backoffice', 'ROLE_CRM_SELLER');
        $this->assertResponseIsSuccessful();
        $this->assertTrue(json_decode((string) $this->client->getResponse()->getContent(), true)['checked']);

        // Remise en l'état
        $this->toggle('Backoffice', 'ROLE_CRM_SELLER');
        $this->assertResponseIsSuccessful();
    }

    public function testTechnicianCannotImpersonateSuperAdmin(): void
    {
        $this->client->loginUser($this->createUser('technician@example.com', ['Technicien', 'Administrator']));

        $this->client->request('GET', '/?_switch_user=superadmin@fastfony.com');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testTechnicianCanImpersonateOrdinaryUser(): void
    {
        $this->client->loginUser($this->createUser('technician@example.com', ['Technicien', 'Administrator']));
        $this->createUser('seller@example.com', ['Commercial']);

        $this->client->request('GET', '/?_switch_user=seller@example.com');

        $this->assertResponseRedirects();
    }

    /**
     * @param list<string> $groupNames
     */
    private function createUser(string $email, array $groupNames): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setEnabled(true);
        foreach ($groupNames as $groupName) {
            $user->addGroup($this->group($groupName));
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->createdUsers[] = $user;

        return $user;
    }

    private function toggle(string $groupName, string $roleName): void
    {
        $this->client->jsonRequest('POST', '/admin/group_role/toggle', [
            'group_id' => $this->group($groupName)->getId(),
            'role_id' => $this->role($roleName)->getId(),
        ]);
    }

    private function group(string $name): Group
    {
        return $this->entityManager->getRepository(Group::class)->findOneBy(['name' => $name]);
    }

    private function role(string $name): Role
    {
        return $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $name]);
    }

    private function superAdmin(): User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'superadmin@fastfony.com']);
    }
}
