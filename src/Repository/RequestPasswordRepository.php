<?php

namespace App\Repository;

use App\Entity\RequestPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestPassword>
 *
 * @method RequestPassword|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestPassword|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestPassword[]    findAll()
 * @method RequestPassword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestPassword::class);
    }

    public function add(RequestPassword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RequestPassword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
