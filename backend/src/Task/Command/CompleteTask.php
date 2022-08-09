<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\Flusher;
use App\Task\Domain\Task;

final class CompleteTask
{
    public function __construct(private readonly Flusher $flusher)
    {
    }

    /**
     * @throws \App\Task\Domain\TaskAlreadyIsDoneException
     */
    public function __invoke(Task $task): void
    {
        $task->markAsDone();

        $this->flusher->flush();
    }
}
