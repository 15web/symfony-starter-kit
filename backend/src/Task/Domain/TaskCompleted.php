<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class TaskCompleted
{
    #[ORM\Column]
    private bool $isCompleted;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt;

    public function __construct(
        bool $isCompleted,
        ?\DateTimeImmutable $completedAt = null,
    ) {
        $this->isCompleted = $isCompleted;
        $this->completedAt = $completedAt;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }
}
