<?php

declare(strict_types=1);

namespace App\Controller\Security\OAuthClient\Github;

use App\Controller\Security\OAuthClient\AbstractConnect;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connect/github', name: 'connect_github', methods: ['GET'])]
class Connect extends AbstractConnect
{
    public function __invoke(string $service = 'github'): RedirectResponse
    {
        $this->scopes = ['user', 'user:email'];

        return parent::__invoke($service);
    }
}
