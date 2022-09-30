<?php

declare(strict_types=1);

namespace App\Entity\OAuth2;

use Doctrine\ORM\Mapping as ORM;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

#[ORM\Entity]
#[ORM\Table('oauth2_client')]
class Client extends AbstractClient
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    protected $identifier;

    protected ?string $secret;

    private array $scopeStrings;

    private array $redirectUriStrings;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getAllowPlainTextPkce(): bool
    {
        return $this->isPlainTextPkceAllowed();
    }

    public function getScopeStrings(): array
    {
        return array_map(static function(Scope $scope): string {
            return $scope->__toString();
        }, $this->getScopes());
    }

    public function setScopeStrings(array $scopeStrings): self
    {
        $this->scopeStrings = $scopeStrings;
        $this->setScopes(...array_map(static function (string $scope): Scope {
            return new Scope($scope);
        }, $scopeStrings));

        return $this;
    }

    public function getRedirectUriStrings(): array
    {
        return array_map(static function(RedirectUri $redirectUri): string {
            return $redirectUri->__toString();
        }, $this->getRedirectUris());
    }

    public function setRedirectUriStrings(array $redirectUriStrings): self
    {
        $this->redirectUriStrings = $redirectUriStrings;
        $this->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
            return new RedirectUri($redirectUri);
        }, $redirectUriStrings));

        return $this;
    }
}
