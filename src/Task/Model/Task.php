<?php

declare(strict_types=1);

namespace App\Task\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
/** @final */
class Task
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Embedded]
    private TaskName $taskName;

    private bool $isCompleted;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $completedAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(TaskName $taskName)
    {
        $this->id = Uuid::v4();
        $this->taskName = $taskName;
        $this->createdAt = new \DateTimeImmutable();
        $this->completedAt = null;
        $this->updatedAt = null;
        $this->isCompleted = false;
    }

    public function changeTaskName(TaskName $taskName): void
    {
        $this->taskName = $taskName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsDone(): void
    {
        if ($this->isCompleted === true) {
            throw new \DomainException('Задача уже выполнена');
        }

        $this->isCompleted = true;
        $this->updatedAt = new \DateTimeImmutable();
        $this->completedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTaskName(): TaskName
    {
        return $this->taskName;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }
}
