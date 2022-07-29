<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
/** @final */
class Task
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Embedded]
    private TaskName $taskName;

    #[ORM\Embedded]
    private TaskCompleted $taskCompleted;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(Uuid $id, TaskName $taskName, Uuid $userId)
    {
        $this->id = $id;
        $this->taskName = $taskName;
        $this->userId = $userId;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
        $this->taskCompleted = new TaskCompleted(isCompleted: false);
    }

    public function changeTaskName(TaskName $taskName): void
    {
        $this->taskName = $taskName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsDone(): void
    {
        if ($this->taskCompleted->isCompleted() === true) {
            throw new TaskAlreadyIsDoneException('Задача уже выполнена');
        }

        $this->taskCompleted = new TaskCompleted(true, new \DateTimeImmutable());
        $this->updatedAt = new \DateTimeImmutable();
    }
}
