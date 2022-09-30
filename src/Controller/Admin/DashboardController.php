<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Entity\OAuth2\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Marshal');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('nav.admin.users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('nav.admin.oauth2Clients', 'fas fa-users', Client::class);
        yield MenuItem::linktoRoute('nav.admin.backToHomepage', 'fas fa-home', 'homepage');
    }
}
