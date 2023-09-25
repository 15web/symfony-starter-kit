<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindUncompletedTasksByUserId;

use DateTimeImmutable;

/**
 * DTO задачи
 */
final readonly class TaskData
{
    public function __construct(
        public string $taskName,
        public DateTimeImmutable $createdAt,
    ) {}
}
