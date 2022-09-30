<?php

namespace App\Repository\OAuth2;

use App\Entity\OAuth2\ClientProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientProfile>
 *
 * @method ClientProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientProfile[]    findAll()
 * @method ClientProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientProfile::class);
    }

    public function add(ClientProfile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClientProfile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
