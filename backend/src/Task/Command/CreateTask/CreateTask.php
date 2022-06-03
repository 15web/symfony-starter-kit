<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\Flusher;
use App\Task\Model\Task;
use App\Task\Model\TaskName;
use App\Task\Model\Tasks;

final class CreateTask
{
    public function __construct(private readonly Tasks $tasks, private readonly Flusher $flusher)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand): void
    {
        $task = new Task(new TaskName($createTaskCommand->taskName));
        $this->tasks->add($task);

        $this->flusher->flush();
    }
}
