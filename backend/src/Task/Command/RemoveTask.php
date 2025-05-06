<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Task\Domain\Task;
use App\Task\Domain\TaskRepository;

/**
 * Хендлер удаления задачи
 */
final readonly class RemoveTask
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function __invoke(Task $task): void
    {
        $this->taskRepository->remove($task);
    }
}
