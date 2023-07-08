<?php

declare(strict_types=1);

namespace App\Mailer\UncompletedTasks\Command;

/**
 * Содержит список невыполненных задач
 */
final readonly class UncompletedTasksMessage
{
    /**
     * @param UncompletedTaskData[] $tasks
     */
    public function __construct(public string $email, public array $tasks)
    {
    }
}
