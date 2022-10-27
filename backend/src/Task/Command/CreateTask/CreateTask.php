<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use App\Task\Domain\Tasks;
use App\User\SignUp\Domain\UserId;

#[AsService]
final class CreateTask
{
    public function __construct(private readonly Flush $flush, private readonly Tasks $tasks)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, TaskId $taskId, UserId $userId): void
    {
        $task = new Task($taskId, new TaskName($createTaskCommand->taskName), $userId);

        $this->tasks->add($task);
        ($this->flush)();
    }
}
