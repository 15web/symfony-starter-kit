<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;

/**
 * Хендлер завершения задачи
 */
final class CompleteTask
{
    /**
     * @throws TaskAlreadyIsDoneException
     */
    public function __invoke(Task $task): void
    {
        $task->markAsDone();
    }
}
