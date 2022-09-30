<?php

namespace App\Entity;

use App\Entity\OAuth2\UserConsent;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var null|string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $active = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserConsent::class, orphanRemoval: true)]
    private Collection $userConsents;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RequestPassword::class, orphanRemoval: true)]
    private Collection $requestPasswords;

    public function __construct()
    {
        $this->userConsents = new ArrayCollection();
        $this->uuid = Uuid::v4();

        if (null === $this->getPassword()) {
            $this->setActive(false);
        }
        $this->requestPasswords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, UserConsent>
     */
    public function getUserConsents(): Collection
    {
        return $this->userConsents;
    }

    public function addUserConsent(UserConsent $userConsent): self
    {
        if (!$this->userConsents->contains($userConsent)) {
            $this->userConsents->add($userConsent);
            $userConsent->setUser($this);
        }

        return $this;
    }

    public function removeUserConsent(UserConsent $userConsent): self
    {
        if ($this->userConsents->removeElement($userConsent)) {
            if ($userConsent->getUser() === $this) {
                $userConsent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RequestPassword>
     */
    public function getRequestPasswords(): Collection
    {
        return $this->requestPasswords;
    }

    public function addRequestPassword(RequestPassword $requestPassword): self
    {
        if (!$this->requestPasswords->contains($requestPassword)) {
            $this->requestPasswords->add($requestPassword);
            $requestPassword->setUser($this);
        }

        return $this;
    }

    public function removeRequestPassword(RequestPassword $requestPassword): self
    {
        if ($this->requestPasswords->removeElement($requestPassword)) {
            // set the owning side to null (unless already changed)
            if ($requestPassword->getUser() === $this) {
                $requestPassword->setUser(null);
            }
        }

        return $this;
    }
}
