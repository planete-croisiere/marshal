<?php

declare(strict_types=1);

namespace App\Controller\Admin\OAuth2Server;

use App\Entity\OAuth2Server\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use League\Bundle\OAuth2ServerBundle\OAuth2Grants;

class ClientCrud extends AbstractCrudController
{
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
                ->hideWhenCreating(),
            ChoiceField::new('scopeStrings')
                ->setChoices(['email' => 'email',  'roles' => 'roles'])
                ->allowMultipleChoices(),
            ChoiceField::new('grantStrings')
                ->setChoices([
                    OAuth2Grants::AUTHORIZATION_CODE => OAuth2Grants::AUTHORIZATION_CODE,
                    OAuth2Grants::CLIENT_CREDENTIALS => OAuth2Grants::CLIENT_CREDENTIALS,
                ])
                ->allowMultipleChoices(),
            ArrayField::new('redirectUriStrings')
                ->hideOnIndex(),
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
