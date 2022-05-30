<?php

declare(strict_types=1);

namespace App\Task\Model;

final class Task
{
    private readonly \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(private TaskName $taskName)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function changeTaskName(TaskName $taskName): void
    {
        $this->taskName = $taskName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getTaskName(): TaskName
    {
        return $this->taskName;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
