<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User\Group;
use App\Entity\User\Role;
use App\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Règles d'attribution des droits « protégés ».
 *
 * Un droit protégé (Super Admin, usurpation d'identité dans Marshal, rôle technique) permet de prendre
 * le contrôle de tous les outils. Seul un détenteur de ROLE_SUPER_ADMIN (groupe « Grand Chef ») peut :
 * - ajouter ou retirer un membre d'un groupe qui porte un droit protégé ;
 * - ajouter ou retirer un droit protégé à un groupe, ou modifier un groupe protégé ;
 * - créer, renommer ou supprimer un rôle protégé ;
 * - modifier ou supprimer un utilisateur qui détient un droit protégé (sinon on pourrait changer son
 *   e-mail puis se connecter à sa place avec un lien de connexion).
 *
 * Sans utilisateur connecté (commandes console, fixtures, parcours anonymes comme la réinitialisation
 * de mot de passe), rien n'est restreint : ces contextes n'offrent pas d'élévation de privilèges.
 */
class PrivilegeGuard
{
    public const SUPER_ADMIN_ROLE = 'ROLE_SUPER_ADMIN';

    public const PROTECTED_ROLES = [
        self::SUPER_ADMIN_ROLE,
        'ROLE_ALLOWED_TO_SWITCH',
        'ROLE_TECHNICAL',
    ];

    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Vrai si l'utilisateur courant peut attribuer ou retirer des droits protégés.
     */
    public function canGrantProtectedPrivileges(): bool
    {
        if (null === $this->security->getUser()) {
            return true;
        }

        return $this->security->isGranted(self::SUPER_ADMIN_ROLE);
    }

    public function isProtectedRoleName(?string $roleName): bool
    {
        return \in_array($roleName, self::PROTECTED_ROLES, true);
    }

    public function isProtectedRole(Role $role): bool
    {
        return $this->isProtectedRoleName($role->getName());
    }

    /**
     * @param iterable<Role> $roles
     */
    public function containsProtectedRole(iterable $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->isProtectedRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isProtectedGroup(Group $group): bool
    {
        return $this->containsProtectedRole($group->getRoles());
    }

    public function isProtectedUser(User $user): bool
    {
        return [] !== array_intersect(self::PROTECTED_ROLES, $user->getRoles());
    }

    /**
     * Vrai si l'utilisateur courant peut modifier ou supprimer cette entité dans l'administration.
     */
    public function canManage(User|Group|Role $subject): bool
    {
        if ($this->canGrantProtectedPrivileges()) {
            return true;
        }

        return match (true) {
            // Chacun peut modifier sa propre fiche ; ses groupes restent contrôlés à l'enregistrement
            $subject instanceof User => !$this->isProtectedUser($subject) || $this->isCurrentUser($subject),
            $subject instanceof Group => !$this->isProtectedGroup($subject),
            $subject instanceof Role => !$this->isProtectedRole($subject),
        };
    }

    public function isCurrentUser(User $user): bool
    {
        $currentUser = $this->security->getUser();

        return $currentUser instanceof User && null !== $user->getId() && $currentUser->getId() === $user->getId();
    }
}
