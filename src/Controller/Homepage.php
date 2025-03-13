<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page\Page;
use App\Entity\User\User;
use App\Repository\Page\PageRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class Homepage extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    // Le slug peut être vide pour la homepage
    /**
     * @return array<string, mixed>
     */
    #[Route('/', name: 'homepage', methods: ['GET'])]
    #[Template('pages/show.html.twig')]
    public function __invoke(
        Request $request,
    ): array|RedirectResponse {
        // Si l'utilisateur qui se connecte n'est associé qu'à une application, et qu'il n'est pas admin
        // on le redirige directement vers cette application
        $user = $this->getUser();
        if ($user instanceof User && 1 === \count($user->getClients()) && !$user->isAdmin()) {
            $clientUrl = $user->getClients()->first()->getUrl();
            if ($clientUrl) {
                return new RedirectResponse($clientUrl);
            }
        }

        return [
            'page' => $this->getHomepage(),
        ];
    }

    private function getHomepage(): ?Page
    {
        try {
            return $this->pageRepository->findHomepage();
        } catch (NonUniqueResultException $exception) {
            throw $this->createNotFoundException(
                'You have multiple homepages configured.',
            );
        }
    }
}
