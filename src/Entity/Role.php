<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
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
}
