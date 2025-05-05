<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Task\Domain\Task;
use App\Task\Domain\TaskName;

/**
 * Хендлер обновления имени задачи
 */
final class UpdateTaskName
{
    public function __invoke(
        Task $task,
        UpdateTaskNameCommand $command,
    ): void {
        $task->changeTaskName(new TaskName($command->taskName));
    }
}
