<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use App\Task\Domain\Tasks;
use App\User\SignUp\Domain\UserId;

/**
 * Хендлер создания задачи
 */
#[AsService]
final readonly class CreateTask
{
    public function __construct(private Tasks $tasks)
    {
    }

    public function __invoke(
        CreateTaskCommand $createTaskCommand,
        TaskId $taskId,
        UserId $userId,
    ): void {
        $task = new Task($taskId, new TaskName($createTaskCommand->taskName), $userId);

        $this->tasks->add($task);
    }
}
