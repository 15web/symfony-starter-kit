<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\Flusher;
use App\Task\Model\Task;
use App\Task\Model\TaskName;
use App\Task\Model\Tasks;
use Symfony\Component\Uid\Uuid;

final class CreateTask
{
    public function __construct(private readonly Tasks $tasks, private readonly Flusher $flusher)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, Uuid $taskId, Uuid $userId): void
    {
        $task = new Task($taskId, new TaskName($createTaskCommand->taskName), $userId);
        $this->tasks->add($task);

        $this->flusher->flush();
    }
}
