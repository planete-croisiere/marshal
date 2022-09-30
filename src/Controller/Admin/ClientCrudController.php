<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Entity\OAuth2\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClientCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly array $availableScopes
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('identifier'),
            TextField::new('secret')
                ->hideOnIndex()
                ->hideOnDetail()
                ->hideWhenUpdating()
                ->setHelp('help.admin.oauth2ClientSecret'),
            ChoiceField::new('scopeStrings')
                ->setChoices(array_combine($this->availableScopes, $this->availableScopes))
                ->allowMultipleChoices(),
            ArrayField::new('redirectUriStrings')
                ->hideOnIndex(),
            BooleanField::new('allowPlainTextPkce'),
            BooleanField::new('active'),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        return new $entityFqcn(
            '',
            $this->generateRandomClientIdentifier(),
            $this->generateRandomClientSecret(),
        );
    }

    /**
     * @throws \Exception
     */
    private function generateRandomClientIdentifier(): string
    {
        return hash('md5', random_bytes(16));
    }

    /**
     * @throws \Exception
     */
    private function generateRandomClientSecret(): string
    {
        return hash('sha512', random_bytes(32));
    }
}
