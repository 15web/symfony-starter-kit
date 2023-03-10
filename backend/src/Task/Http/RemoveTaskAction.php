<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Domain\Task;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка удаления задачи
 */
#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/remove', methods: ['POST'])]
#[AsController]
final class RemoveTaskAction
{
    public function __construct(
        private readonly RemoveTask $removeTask,
        private readonly Flush $flush,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Task $task): SuccessResponse
    {
        ($this->removeTask)($task);

        ($this->flush)();

        $this->logger->info('Задача удалена', [
            'id' => $task->getTaskId(),
            self::class => __FUNCTION__,
        ]);

        return new SuccessResponse();
    }
}
