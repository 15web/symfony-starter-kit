<?php

declare(strict_types=1);

namespace App\Mailer\UncompletedTasks\Command;

use DateTimeImmutable;

/**
 * DTO задачи
 */
final readonly class UncompletedTaskData
{
    public function __construct(
        public string $taskName,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
