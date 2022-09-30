<?php

namespace App\Entity;

use App\Repository\RequestPasswordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequestPasswordRepository::class)]
class RequestPassword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expireAt = null;

    #[ORM\ManyToOne(inversedBy: 'requestPasswords')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->token = Uuid::v4();
        $this->expireAt = (new \DateTimeImmutable())->modify('+2 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpireAt(): ?\DateTimeImmutable
    {
        return $this->expireAt;
    }

    public function setExpireAt(\DateTimeImmutable $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
