<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Entity\User\Group;
use App\Entity\User\Role;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProtectedPrivilegeListenerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        // Chaque test est annulé en fin d'exécution pour laisser les fixtures intactes
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testAdministratorCannotJoinSuperAdminGroup(): void
    {
        $administrator = $this->createUser('administrator@example.com', 'Administrator');
        $this->loginAs($administrator);

        $administrator->addGroup($this->group(Group::SUPER_ADMIN_NAME));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotJoinTechnicianGroup(): void
    {
        $administrator = $this->createUser('administrator@example.com', 'Administrator');
        $this->loginAs($administrator);

        $administrator->addGroup($this->group('Technicien'));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotRemoveSuperAdminMember(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->superAdmin()->removeGroup($this->group(Group::SUPER_ADMIN_NAME));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotGrantSuperAdminRoleToAGroup(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->group('Administrator')->addRole($this->role('ROLE_SUPER_ADMIN'));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotModifySuperAdminGroupRights(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->group(Group::SUPER_ADMIN_NAME)->addRole($this->role('ROLE_CRM_SELLER'));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotRenameARoleIntoAProtectedRole(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->role('ROLE_CRM_SELLER')->setName('ROLE_TECHNICAL');

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotCreateAProtectedRole(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->entityManager->persist((new Role())->setName('ROLE_SUPER_ADMIN')->setDescription('Copy'));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotChangeSuperAdminEmail(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->superAdmin()->setEmail('attacker@example.com');

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotToggleSuperAdminActivation(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        // D'autres tests modifient l'état du compte : on inverse la valeur courante
        $superAdmin = $this->superAdmin();
        $superAdmin->setEnabled(!$superAdmin->isEnabled());

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCannotDeleteSuperAdminGroup(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $this->entityManager->remove($this->group(Group::SUPER_ADMIN_NAME));

        $this->expectException(AccessDeniedException::class);
        $this->entityManager->flush();
    }

    public function testAdministratorCanManageOrdinaryRights(): void
    {
        $this->loginAs($this->createUser('administrator@example.com', 'Administrator'));

        $seller = $this->createUser('seller@example.com', 'User');
        $seller->addGroup($this->group('Commercial'))->setEnabled(false);
        $this->group('Backoffice')->addRole($this->role('ROLE_CRM_SELLER'));
        $this->entityManager->flush();

        $this->assertTrue($seller->hasRole('ROLE_CRM_SELLER'));
    }

    public function testAdministratorCanEditOwnAccount(): void
    {
        $technician = $this->createUser('technician@example.com', 'Technicien');
        $this->loginAs($technician);

        $technician->setEnabled(false);
        $this->entityManager->flush();

        $this->assertFalse($technician->isEnabled());
    }

    public function testSuperAdminCanGrantSuperAdminGroup(): void
    {
        $administrator = $this->createUser('administrator@example.com', 'Administrator');
        $this->loginAs($this->superAdmin());

        $administrator->addGroup($this->group(Group::SUPER_ADMIN_NAME));
        $this->entityManager->flush();

        $this->assertTrue($administrator->hasRole('ROLE_SUPER_ADMIN'));
    }

    public function testWithoutAuthenticatedUserNothingIsRestricted(): void
    {
        $administrator = $this->createUser('administrator@example.com', 'Administrator');

        $administrator->addGroup($this->group(Group::SUPER_ADMIN_NAME));
        $this->entityManager->flush();

        $this->assertTrue($administrator->hasRole('ROLE_SUPER_ADMIN'));
    }

    private function createUser(string $email, string $groupName): User
    {
        // Création hors de toute session : le contrôle ne s'applique pas encore
        self::getContainer()->get(TokenStorageInterface::class)->setToken(null);

        $user = (new User())
            ->setEmail($email)
            ->setEnabled(true)
            ->addGroup($this->group($groupName));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function loginAs(User $user): void
    {
        self::getContainer()->get(TokenStorageInterface::class)->setToken(
            new UsernamePasswordToken($user, 'main', $user->getRoles()),
        );
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
