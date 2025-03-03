<?php

declare(strict_types=1);

namespace App\Entity\Parameter;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Entity\CommonProperties;
use App\Repository\Parameter\ParameterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/internal/parameters',
        ),
        new Patch(
            uriTemplate: '/internal/parameters/{id}',
        ),
    ],
    normalizationContext: ['groups' => ['internal:parameter:read']],
    security: "is_granted('ROLE_ADMIN')",
)]
#[ORM\Entity(repositoryClass: ParameterRepository::class)]
class Parameter
{
    use CommonProperties\Required\AutoGeneratedId;
    use CommonProperties\Required\KeyValue;
    public const LIST_TYPES = [
        'text',
        'bool',
        'email',
        'url',
    ];

    #[Groups(['internal:parameter:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[Groups(['internal:parameter:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $help = null;

    #[Groups(['internal:parameter:read'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'parameters')]
    private ?ParameterCategory $category = null;

    #[Groups(['internal:parameter:read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['internal:parameter:read'])]
    public function getKey(): string
    {
        return $this->key;
    }

    #[Groups(['internal:parameter:read'])]
    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): ?ParameterCategory
    {
        return $this->category;
    }

    #[Groups(['internal:parameter:read'])]
    #[SerializedName('category')]
    public function getCategoryName(): ?string
    {
        return $this->category ? $this->category->getName() : null;
    }

    public function setCategory(?ParameterCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}
