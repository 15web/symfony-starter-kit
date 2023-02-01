<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use Psr\Log\LoggerInterface;

/**
 * Хендлер завершения задачи
 */
#[AsService]
final class CompleteTask
{
    public function __construct(
        private readonly Flush $flush,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TaskAlreadyIsDoneException
     */
    public function __invoke(Task $task): void
    {
        $task->markAsDone();

        ($this->flush)();

        $this->logger->info('Задача завершена', [
            'id' => $task->getTaskId(),
            self::class => __FUNCTION__,
        ]);
    }
}
