<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindById;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * DTO задачи
 */
final class TaskData
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $taskName,
        public readonly bool $isCompleted,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $completedAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {
    }
}
