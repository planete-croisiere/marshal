<?php

declare(strict_types=1);

namespace App\Controller\Admin\Parameter;

use App\Entity\Parameter\ParameterCategory;
use App\Security\RoleName;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(RoleName::TECHNICAL)]
class ParameterCategoryCrud extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ParameterCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInPlural('Parameter categories')
        ;
    }
}
