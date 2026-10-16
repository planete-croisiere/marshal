<?php

declare(strict_types=1);

namespace App\EventListener\Security;

use App\Entity\User\Group;
use App\Entity\User\Role;
use App\Entity\User\User;
use App\Security\PrivilegeGuard;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Dernier rempart contre l'auto-attribution des droits protégés (voir PrivilegeGuard).
 *
 * Contrôle chaque enregistrement en base, quel que soit l'écran d'origine (fiche utilisateur, groupe,
 * rôle, matrice groupes/rôles, interrupteur de la liste) : l'administration peut masquer des choix,
 * mais c'est ici que l'interdiction est garantie.
 */
#[AsDoctrineListener(event: Events::onFlush)]
class ProtectedPrivilegeListener
{
    public function __construct(
        private readonly PrivilegeGuard $privilegeGuard,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if ($this->privilegeGuard->canGrantProtectedPrivileges()) {
            return;
        }

        $unitOfWork = $args->getObjectManager()->getUnitOfWork();

        $this->checkEntityInsertions($unitOfWork);
        $this->checkEntityUpdates($unitOfWork);
        $this->checkEntityDeletions($unitOfWork);
        $this->checkCollectionChanges($unitOfWork);
    }

    private function checkEntityInsertions(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Role && $this->privilegeGuard->isProtectedRole($entity)) {
                $this->deny('Only a super administrator can create the role '.$entity->getName().'.');
            }
        }
    }

    private function checkEntityUpdates(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Role) {
                // On contrôle l'ancien et le nouveau nom : renommer un rôle ordinaire en ROLE_SUPER_ADMIN
                // reviendrait à l'attribuer à tous les groupes qui le portent.
                $names = $unitOfWork->getEntityChangeSet($entity)['name'] ?? [$entity->getName()];
                foreach ($names as $name) {
                    if ($this->privilegeGuard->isProtectedRoleName($name)) {
                        $this->deny('Only a super administrator can modify the role '.$name.'.');
                    }
                }
            }

            if ($entity instanceof Group && $this->isProtectedGroup($entity)) {
                $this->deny('Only a super administrator can modify the group '.$entity->getName().'.');
            }

            if ($entity instanceof User && !$this->privilegeGuard->canManage($entity)) {
                $this->deny('Only a super administrator can modify the user '.$entity->getEmail().'.');
            }
        }
    }

    private function checkEntityDeletions(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Role && $this->privilegeGuard->isProtectedRole($entity)) {
                $this->deny('Only a super administrator can delete the role '.$entity->getName().'.');
            }

            if ($entity instanceof Group && $this->isProtectedGroup($entity)) {
                $this->deny('Only a super administrator can delete the group '.$entity->getName().'.');
            }

            if ($entity instanceof User && !$this->privilegeGuard->canManage($entity)) {
                $this->deny('Only a super administrator can delete the user '.$entity->getEmail().'.');
            }
        }
    }

    private function checkCollectionChanges(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            $this->checkGroupCollection(
                $collection,
                [...$collection->getInsertDiff(), ...$collection->getDeleteDiff()],
            );
        }

        // Collection remplacée ou vidée : tout son contenu d'origine est retiré
        foreach ($unitOfWork->getScheduledCollectionDeletions() as $collection) {
            $this->checkGroupCollection($collection, $collection->getSnapshot());
        }
    }

    /**
     * @param PersistentCollection<array-key, object> $collection
     * @param array<object>                           $changedElements éléments ajoutés ou retirés
     */
    private function checkGroupCollection(PersistentCollection $collection, array $changedElements): void
    {
        $owner = $collection->getOwner();
        if (!$owner instanceof Group || [] === $changedElements) {
            return;
        }

        $field = $collection->getMapping()->fieldName;

        if ('roles' === $field) {
            /** @var array<Role> $changedElements */
            if ($this->isProtectedGroup($owner) || $this->privilegeGuard->containsProtectedRole($changedElements)) {
                $this->deny('Only a super administrator can change the rights of the group '.$owner->getName().'.');
            }
        }

        // Côté propriétaire de la relation groupe/utilisateur : couvre aussi l'ajout depuis la fiche utilisateur
        if ('users' === $field && $this->isProtectedGroup($owner)) {
            $this->deny('Only a super administrator can change the members of the group '.$owner->getName().'.');
        }
    }

    /**
     * Un groupe est protégé s'il porte un droit protégé, avant ou après la modification en cours.
     */
    private function isProtectedGroup(Group $group): bool
    {
        if ($this->privilegeGuard->isProtectedGroup($group)) {
            return true;
        }

        $roles = $group->getRoles();

        return $roles instanceof PersistentCollection
            && $this->privilegeGuard->containsProtectedRole($roles->getSnapshot());
    }

    private function deny(string $message): never
    {
        throw new AccessDeniedException($message);
    }
}
