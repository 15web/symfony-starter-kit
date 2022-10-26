<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\TaskName;

#[AsService]
final class UpdateTaskName
{
    public function __construct(private readonly Flush $flush)
    {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): void
    {
        $task->changeTaskName(new TaskName($command->taskName));

        ($this->flush)();
    }
}
