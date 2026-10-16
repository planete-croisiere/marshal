<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User\Group;
use App\Repository\SaveAndRemoveMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 */
class GroupRepository extends ServiceEntityRepository
{
    use SaveAndRemoveMethod;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Exclut d'une requête sur les groupes ceux qui portent l'un des rôles donnés.
     *
     * @param list<string> $roleNames
     */
    public static function excludeGroupsWithRoles(QueryBuilder $queryBuilder, string $groupAlias, array $roleNames): QueryBuilder
    {
        return $queryBuilder
            ->andWhere(\sprintf(
                '%s.id NOT IN (SELECT excluded_group.id FROM %s excluded_group JOIN excluded_group.roles excluded_role WHERE excluded_role.name IN (:excluded_role_names))',
                $groupAlias,
                Group::class,
            ))
            ->setParameter('excluded_role_names', $roleNames);
    }
}
