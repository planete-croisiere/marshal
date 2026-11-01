<?php

declare(strict_types=1);

namespace App\EventListener\Security;

use App\Entity\User\User;
use App\Repository\User\UserRepository;
use App\Security\OAuthClient\AbstractAuthenticator as SocialAuthenticator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Authenticator\LoginLinkAuthenticator;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Première activation d'un compte créé par son titulaire (inscription ou connexion sociale).
 *
 * Seuls les moyens qui prouvent la possession de l'adresse email activent le compte : le lien de
 * connexion signé reçu par email et la connexion sociale. Le cookie remember-me et le mot de passe
 * n'activent jamais. Un compte désactivé par un administrateur n'est pas en attente d'activation :
 * il reste refusé par App\Security\UserChecker.
 *
 * Priorité 512 : après la limitation des tentatives (2080), avant le UserChecker (256).
 */
#[AsEventListener(event: CheckPassportEvent::class, priority: 512)]
class AccountActivation
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(CheckPassportEvent $event): void
    {
        $authenticator = $event->getAuthenticator();
        if (!$authenticator instanceof LoginLinkAuthenticator && !$authenticator instanceof SocialAuthenticator) {
            return;
        }

        // Pour le lien de connexion, charger l'utilisateur vérifie aussi la signature du lien
        $user = $event->getPassport()->getUser();
        if (!$user instanceof User || !$user->isPendingActivation()) {
            return;
        }

        $user->activate();
        $this->userRepository->save($user);
    }
}
