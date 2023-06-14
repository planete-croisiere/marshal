<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Handler\UserPasswordHandler;
use App\Model\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use function Symfony\Component\Translation\t;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('uuid')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('phoneNumber'),
            TextField::new('password')
                ->hideOnIndex()
                ->hideWhenCreating()
                ->setLabel('Encoded password')
                ->setHelp('help.admin.userPassword'),
            ChoiceField::new('roles')
                ->allowMultipleChoices()
                ->setTranslatableChoices($this->getRoleChoices()),
            BooleanField::new('active'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('active')
            ->add('email')
            ->add('firstName')
            ->add('lastName')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendPasswordChoice = Action::new('sendChoicePassword', 'cta.admin.user.sendChoicePassword', 'fa fa-envelope')
            ->linkToCrudAction('sendChoicePassword');

        $actions->add(Crud::PAGE_INDEX, $sendPasswordChoice);

        return parent::configureActions($actions);
    }

    public function sendChoicePassword(
        AdminContext $context,
        UserPasswordHandler $userPasswordHandler,
    ) {
        try {
            $userPasswordHandler->sendEmailForChoose($context->getEntity()->getInstance());

            $this->addFlash('success', 'flashSuccess.admin.sendEmailForChoose');
        } catch (TransportExceptionInterface $transportException) {
            $this->addFlash('error', $transportException->getMessage());
        }

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    private function getRoleChoices(): array
    {
        $choices = [];
        foreach (Role::cases() as $role) {
            $choices[$role->name] = t($role->value);
        }

        return $choices;
    }
}
