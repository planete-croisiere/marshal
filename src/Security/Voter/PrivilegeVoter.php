<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User\Group;
use App\Entity\User\Role;
use App\Entity\User\User;
use App\Security\PrivilegeGuard;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Permission des actions d'administration (modifier, supprimer) sur les utilisateurs, groupes et rôles.
 *
 * EasyAdmin passe l'entité concernée, ou le nom de sa classe pour les actions sans entité (création,
 * liste) : ces dernières restent autorisées, l'enregistrement est contrôlé par ProtectedPrivilegeListener.
 *
 * @extends Voter<string, mixed>
 */
class PrivilegeVoter extends Voter
{
    public const MANAGE = 'PRIVILEGE_MANAGE';

    public function __construct(
        private readonly PrivilegeGuard $privilegeGuard,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MANAGE === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($subject instanceof User || $subject instanceof Group || $subject instanceof Role) {
            return $this->privilegeGuard->canManage($subject);
        }

        return true;
    }
}
