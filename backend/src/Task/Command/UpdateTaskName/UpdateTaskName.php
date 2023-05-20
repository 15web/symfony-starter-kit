<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\TaskName;

/**
 * Хендлер обновления имени задачи
 */
#[AsService]
final class UpdateTaskName
{
    public function __invoke(Task $task, #[ApiRequest] UpdateTaskNameCommand $command): void
    {
        $task->changeTaskName(new TaskName($command->taskName));
    }
}
