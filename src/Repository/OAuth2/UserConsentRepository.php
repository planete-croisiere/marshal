<?php

namespace App\Repository\OAuth2;

use App\Entity\OAuth2\UserConsent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserConsent>
 *
 * @method UserConsent|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserConsent|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserConsent[]    findAll()
 * @method UserConsent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserConsent::class);
    }

    public function add(UserConsent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserConsent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
