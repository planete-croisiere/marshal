<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\VichImageField;
use App\Entity\User\Profile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProfileCrud extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Profile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            TextField::new('firstName'),
            FormField::addColumn(6),
            TextField::new('lastName'),
            FormField::addColumn(6),
            VichImageField::new('photoFile')
                ->onlyOnForms(),
            FormField::addColumn(6),
            TelephoneField::new('phoneNumber'),
        ];
    }
}
