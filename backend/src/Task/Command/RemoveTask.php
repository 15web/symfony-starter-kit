<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\Tasks;

/**
 * Хендлер удаления задачи
 */
#[AsService]
final class RemoveTask
{
    public function __construct(private readonly Tasks $tasks)
    {
    }

    public function __invoke(Task $task): void
    {
        $this->tasks->remove($task);
    }
}
