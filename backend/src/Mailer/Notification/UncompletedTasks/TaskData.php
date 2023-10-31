<?php

declare(strict_types=1);

namespace App\Mailer\Notification\UncompletedTasks;

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
