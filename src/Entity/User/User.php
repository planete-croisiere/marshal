<?php

declare(strict_types=1);

namespace App\Entity\User;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\CommonProperties;
use App\Entity\OAuth2Server\Client;
use App\Repository\User\UserRepository;
use App\State\SelfUserProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/users/me',
            normalizationContext: ['groups' => ['user:profile:read']],
            security: "is_granted('ROLE_API') and object == user",
            provider: SelfUserProvider::class,
        ),
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CommonProperties\Required\AutoGeneratedId;
    use CommonProperties\Required\Email;
    use CommonProperties\Required\Enabled;

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    /**
     * @var Collection<int, RequestPassword>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'users')]
    private Collection $groups;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    #[Groups([
        'user:profile:read',
    ])]
    #[ApiProperty(security: "is_granted('ROLE_OAUTH2_PROFILE')")]
    private ?Profile $profile = null;

    #[ORM\OneToMany(
        targetEntity: RequestPassword::class,
        mappedBy: 'user',
        orphanRemoval: true,
    )]
    private Collection $requestPasswords;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\ManyToMany(targetEntity: Client::class)]
    #[ORM\InverseJoinColumn(name: 'client_identifier', referencedColumnName: 'identifier')]
    private Collection $clients;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->requestPasswords = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[Groups([
        'user:profile:read',
    ])]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[Groups([
        'user:profile:read',
    ])]
    #[ApiProperty(security: "is_granted('ROLE_OAUTH2_EMAIL')")]
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    #[Groups([
        'user:profile:read',
    ])]
    #[ApiProperty(security: "is_granted('ROLE_OAUTH2_ROLES')")]
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        $roles = ['ROLE_USER'];

        foreach ($this->getGroups() as $group) {
            foreach ($group->getRoles() as $role) {
                $roles[] = $role->getName();
            }
        }

        return array_values(array_unique($roles));
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addUser($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            $group->removeUser($this);
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

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
        $this->requestPasswords->removeElement($requestPassword);

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        $this->clients->removeElement($client);

        return $this;
    }
}
