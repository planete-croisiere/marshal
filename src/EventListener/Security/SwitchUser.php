<?php

declare(strict_types=1);

namespace App\EventListener\Security;

use App\Entity\User\User;
use App\Security\PrivilegeGuard;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

/**
 * Seul un super administrateur peut usurper l'identité d'un utilisateur qui détient un droit protégé :
 * sinon, prendre l'identité d'un « Grand Chef » permettrait de s'attribuer ses droits.
 */
#[AsEventListener(event: SwitchUserEvent::class)]
class SwitchUser
{
    public function __construct(
        private readonly PrivilegeGuard $privilegeGuard,
    ) {
    }

    public function __invoke(SwitchUserEvent $event): void
    {
        $token = $event->getToken();
        $targetUser = $event->getTargetUser();

        // La sortie d'usurpation (_exit) restaure le jeton d'origine : rien à contrôler
        if (!$token instanceof SwitchUserToken || !$targetUser instanceof User) {
            return;
        }

        if (!$this->privilegeGuard->isProtectedUser($targetUser)) {
            return;
        }

        if (!\in_array(PrivilegeGuard::SUPER_ADMIN_ROLE, $token->getOriginalToken()->getRoleNames(), true)) {
            throw new AccessDeniedException('Only a super administrator can impersonate '.$targetUser->getEmail().'.');
        }
    }
}
