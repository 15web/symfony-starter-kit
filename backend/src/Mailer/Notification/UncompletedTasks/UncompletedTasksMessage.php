<?php

declare(strict_types=1);

namespace App\Mailer\Notification\UncompletedTasks;

use App\Task\Query\Task\FindUncompletedTasksByUserId\TaskData;

/**
 * Содержит список невыполненных задач
 */
final readonly class UncompletedTasksMessage
{
    /**
     * @param TaskData[] $tasks
     */
    public function __construct(public string $email, public array $tasks)
    {
    }
}
