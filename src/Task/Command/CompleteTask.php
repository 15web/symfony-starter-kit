<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\Flusher;
use App\Task\Model\Task;

final class CompleteTask
{
    public function __construct(private readonly Flusher $flusher)
    {
    }

    public function __invoke(Task $task): void
    {
        $task->markAsDone();

        $this->flusher->flush();
    }
}
