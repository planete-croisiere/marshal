<?php

declare(strict_types=1);

namespace App\Controller\Security\OAuthClient\Google;

use App\Controller\Security\OAuthClient\AbstractConnect;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connect/google', name: 'connect_google', methods: ['GET'])]
class Connect extends AbstractConnect
{
    public function __invoke(string $service = 'google'): RedirectResponse
    {
        $this->scopes = ['https://www.googleapis.com/auth/userinfo.email'];

        return parent::__invoke($service);
    }
}
