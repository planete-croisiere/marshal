<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    use CommonProperties\Description;
    use CommonProperties\Required\AutoGeneratedId;

    #[Assert\Regex(
        pattern: '/^ROLE_/',
        message: "The role name must start with 'ROLE_'.",
    )]
    #[Assert\NotBlank]
    #[ORM\Column(length: 60)]
    private string $name;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'roles')]
    private Collection $groups;

    #[ORM\ManyToOne(inversedBy: 'role')]
    private ?RoleCategory $category = null;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDescription();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?RoleCategory
    {
        return $this->category;
    }

    public function setCategory(?RoleCategory $category): static
    {
        $this->category = $category;

        return $this;
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
            $group->addRole($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        $this->groups->removeElement($group);

        return $this;
    }
}
