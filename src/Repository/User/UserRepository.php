<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User\Group;
use App\Entity\User\User;
use App\Repository\SaveAndRemoveMethodTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    use SaveAndRemoveMethodTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countEnabled(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.enabled = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function createSuperAdmin(string $email, bool $enabled = false): User
    {
        $superAdminGroup = $this->getEntityManager()
            ->getRepository(Group::class)
            ->findOneBy(['name' => Group::SUPER_ADMIN_NAME]);

        if (null === $superAdminGroup) {
            throw new EntityNotFoundException('Super Admin group not found.');
        }

        $user = $this->create($email);
        $user
            ->setEnabled($enabled)
            ->addGroup($superAdminGroup)
        ;

        $this->save($user);

        return $user;
    }

    public function create(string $email): User
    {
        $user = new User();
        $user->setEmail($email);

        // By default, the user is in group with onRegistration = true
        $groups = $this->getEntityManager()
            ->getRepository(Group::class)
            ->findBy(['onRegistration' => true]);

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $this->save($user);

        return $user;
    }

    public function updatePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user);
    }
}
