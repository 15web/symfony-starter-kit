<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\Tasks;
use Psr\Log\LoggerInterface;

/**
 * Хендлер удаления задачи
 */
#[AsService]
final class RemoveTask
{
    public function __construct(
        private readonly Flush $flush,
        private readonly Tasks $tasks,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Task $task): void
    {
        $this->tasks->remove($task);
        ($this->flush)();

        $this->logger->info('Задача удалена', [
            'id' => $task->getTaskId(),
            self::class => __FUNCTION__,
        ]);
    }
}
