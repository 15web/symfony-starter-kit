<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\Flusher;
use App\Task\Domain\Task;
use App\Task\Domain\TaskName;

final class UpdateTaskName
{
    public function __construct(private readonly Flusher $flusher)
    {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): void
    {
        $task->changeTaskName(new TaskName($command->taskName));

        $this->flusher->flush();
    }
}
