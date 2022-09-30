<?php

namespace App\Entity\OAuth2;

use App\Entity\User;
use App\Repository\OAuth2\UserConsentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserConsentRepository::class)]
#[ORM\Table('oauth2_user_consent')]
class UserConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expires = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $scopes = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(referencedColumnName: 'identifier', nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'userConsents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getExpires(): ?\DateTimeImmutable
    {
        return $this->expires;
    }

    public function setExpires(?\DateTimeImmutable $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(?array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
