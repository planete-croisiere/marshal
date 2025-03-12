<?php

declare(strict_types=1);

namespace App\Entity\Scheduler;

use App\Entity\CommonProperties;
use App\Repository\Scheduler\RunRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: RunRepository::class)]
#[ORM\Table(name: 'scheduler_run')]
class Run
{
    use CommonProperties\Required\AutoGeneratedId;
    use TimestampableEntity;

    /**
     * @var string Identifier of the message context
     */
    #[ORM\Column(length: 255)]
    private string $messageContextId;

    /**
     * @var string The unix timestamp date when the run was executed
     */
    #[ORM\Column]
    private string $runDate;

    #[ORM\Column(name: '`trigger`')]
    private string $trigger;

    #[ORM\Column]
    private string $scheduler;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string $input;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $failureOutput = null;

    #[ORM\Column(name: '`terminated`')]
    private bool $terminated = false;

    public function getMessageContextId(): string
    {
        return $this->messageContextId;
    }

    public function setMessageContextId(string $messageContextId): static
    {
        $this->messageContextId = $messageContextId;

        return $this;
    }

    public function getRunDate(): string
    {
        return $this->runDate;
    }

    public function getRunDateFormatted(): \DateTime
    {
        return \DateTime::createFromFormat('U', $this->runDate);
    }

    public function setRunDate(string $runDate): static
    {
        $this->runDate = $runDate;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): static
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getScheduler(): string
    {
        return $this->scheduler;
    }

    public function setScheduler(string $scheduler): static
    {
        $this->scheduler = $scheduler;

        return $this;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getInputObject(): object
    {
        return unserialize($this->input);
    }

    public function setInput(string $input): static
    {
        $this->input = $input;

        return $this;
    }

    public function hasFailureOutput(): bool
    {
        return null !== $this->failureOutput;
    }

    public function getFailureOutput(): ?string
    {
        return $this->failureOutput;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFailureOutputObject(): ?array
    {
        return $this->failureOutput ? unserialize($this->failureOutput) : null;
    }

    public function setFailureOutput(?string $failureOutput): static
    {
        $this->failureOutput = $failureOutput;

        return $this;
    }

    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    public function setTerminated(bool $terminated): static
    {
        $this->terminated = $terminated;

        return $this;
    }
}
