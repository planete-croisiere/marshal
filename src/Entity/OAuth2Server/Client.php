<?php

declare(strict_types=1);

namespace App\Entity\OAuth2Server;

use Doctrine\ORM\Mapping as ORM;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

#[ORM\Entity]
#[ORM\Table('oauth2_client')]
class Client extends AbstractClient
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    public string $identifier;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getScopeStrings(): array
    {
        return array_map(static function (Scope $scope): string {
            return $scope->__toString();
        }, $this->getScopes());
    }

    public function setScopeStrings(array $scopeStrings): self
    {
        $this->setScopes(...array_map(static function (string $scope): Scope {
            return new Scope($scope);
        }, $scopeStrings));

        return $this;
    }

    public function getGrantStrings(): array
    {
        return array_map(static function (Grant $grant): string {
            return $grant->__toString();
        }, $this->getGrants());
    }

    public function setGrantStrings(array $grantStrings): self
    {
        $this->setGrants(...array_map(static function (string $grant): Grant {
            return new Grant($grant);
        }, $grantStrings));

        return $this;
    }

    public function getRedirectUriStrings(): array
    {
        return array_map(static function (RedirectUri $redirectUri): string {
            return $redirectUri->__toString();
        }, $this->getRedirectUris());
    }

    public function setRedirectUriStrings(array $redirectUriStrings): self
    {
        $this->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
            return new RedirectUri($redirectUri);
        }, $redirectUriStrings));

        return $this;
    }
}
