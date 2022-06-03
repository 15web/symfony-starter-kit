<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\Flusher;
use App\Task\Model\Task;
use App\Task\Model\Tasks;

final class RemoveTask
{
    public function __construct(private readonly Tasks $tasks, private readonly Flusher $flusher)
    {
    }

    public function __invoke(Task $task): void
    {
        $this->tasks->remove($task);
        $this->flusher->flush();
    }
}
