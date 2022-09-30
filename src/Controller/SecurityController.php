<?php

namespace App\Controller;

use App\Entity\OAuth2\Client;
use App\Entity\OAuth2\UserConsent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/consent', name: 'app_consent', methods: ['GET', 'POST'])]
    public function consent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientId = $request->query->get('client_id');
        if (!$clientId || !ctype_alnum($clientId) || !$this->getUser()) {
            throw new AccessDeniedException();
        }

        $client = $entityManager->getRepository(Client::class)->findOneBy(['identifier' => $clientId]);
        if (!$client) {
            throw new AccessDeniedException();
        }

        $requestedScopes = explode(' ', $request->query->get('scope'));
        $clientScopes = $client->getScopes();

        // Check all requested scopes are in the client scopes
        if (count(array_diff($requestedScopes, $clientScopes)) > 0) {
            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        // Check if the user has already consented to the scopes
        /** @var User $user */
        $user = $this->getUser();
        $userConsents = $user->getUserConsents()->filter(
            fn (UserConsent $consent) => $consent->getClient() === $client
        )->first() ?: null;
        $userScopes = $userConsents?->getScopes() ?? [];

        // If user has already consented to the scopes, give consent
        if (count(array_diff($requestedScopes, $userScopes)) === 0) {
            $request->getSession()->set('consent_granted', true);
            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        // Remove the scopes to which the user has already consented
        $requestedScopes = array_diff($requestedScopes, $userScopes);
        if ($request->isMethod('POST')) {
            if ($request->request->get('consent') === 'yes') {
                $request->getSession()->set('consent_granted', true);
                // Add the requested scopes to the user's scopes
                $consents = $userConsents ?? new UserConsent();
                $consents->setScopes(array_merge($requestedScopes, $userScopes))
                    ->setClient($client)
                    ->setCreated(new \DateTimeImmutable())
                    ->setExpires(new \DateTimeImmutable('+10 years'))
                    ->setIpAddress($request->getClientIp());

                $user->addUserConsent($consents);

                $entityManager->persist($consents);
                $entityManager->flush();
            }

            if ($request->request->get('consent') === 'no') {
                $request->getSession()->set('consent_granted', false);
            }

            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }
        return $this->render('security/consent.html.twig', [
            'scopes' => $requestedScopes,
            'existing_scopes' => $userScopes,
            'client' => $client,
        ]);
    }
}
