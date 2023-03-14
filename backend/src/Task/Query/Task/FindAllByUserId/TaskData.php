<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * DTO задачи
 */
final readonly class TaskData
{
    public function __construct(
        public Uuid $id,
        public string $taskName,
        public bool $isCompleted,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
