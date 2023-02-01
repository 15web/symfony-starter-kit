<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\TaskName;
use Psr\Log\LoggerInterface;

/**
 * Хендлер обновления имени задачи
 */
#[AsService]
final class UpdateTaskName
{
    public function __construct(
        private readonly Flush $flush,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): void
    {
        $task->changeTaskName(new TaskName($command->taskName));

        ($this->flush)();

        $this->logger->info('Задача обновлена', [
            'id' => $task->getTaskId(),
            self::class => __FUNCTION__,
        ]);
    }
}
