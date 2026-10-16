<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User\Group;
use App\Security\PrivilegeGuard;
use App\Security\Voter\PrivilegeVoter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class GroupCrud extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly PrivilegeGuard $privilegeGuard,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Group::class;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            BooleanField::new('onRegistration')
                ->hideOnIndex()
                ->hideOnForm(),
            CollectionField::new('roles')
                ->hideOnForm(),
            AssociationField::new('roles')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                // Les rôles protégés ne sont proposés qu'aux super administrateurs
                ->setQueryBuilder(fn (QueryBuilder $queryBuilder) => $this->privilegeGuard->canGrantProtectedPrivileges()
                    ? $queryBuilder
                    : $queryBuilder
                        ->andWhere($queryBuilder->getRootAliases()[0].'.name NOT IN (:protected_role_names)')
                        ->setParameter('protected_role_names', PrivilegeGuard::PROTECTED_ROLES))
                ->hideOnIndex(),
            AssociationField::new('users')
                ->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Groups')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $rolesCrudAction = Action::new('roles', 'Roles', 'fa fa-tags')
            ->linkToUrl(
                $this->adminUrlGenerator
                    ->setController(RoleCrud::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            )
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $rolesCrudAction)
            ->setPermission(Action::EDIT, PrivilegeVoter::MANAGE)
            ->setPermission(Action::DELETE, PrivilegeVoter::MANAGE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('roles')
        ;
    }
}
