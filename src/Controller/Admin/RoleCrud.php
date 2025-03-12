<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class RoleCrud extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('description'),
            AssociationField::new('category'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $groupsCrudAction = Action::new('groups', 'Groups', 'fa fa-users')
            ->linkToUrl(
                $this->adminUrlGenerator
                    ->setController(GroupCrud::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            )
            ->createAsGlobalAction();

        $roleCategoriesCrudAction = Action::new('roleCategories', 'Categories', 'fa fa-gear')
            ->linkToUrl($this->adminUrlGenerator
                ->setController(RoleCategoryCrud::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            )
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $groupsCrudAction)
            ->add(Crud::PAGE_INDEX, $roleCategoriesCrudAction)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
        ;
    }
}
