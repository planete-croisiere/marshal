<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Un compte désactivé ne s'authentifie jamais, quel que soit le moyen (lien de connexion,
        // cookie remember-me, connexion sociale, mot de passe, jeton OAuth2) et qu'il ait ou non
        // un mot de passe. La première activation d'un compte en attente est faite avant ce
        // contrôle par App\EventListener\Security\AccountActivation.
        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('Inactive account');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // Do nothing
    }
}
