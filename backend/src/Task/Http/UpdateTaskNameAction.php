<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\UpdateTaskName\UpdateTaskName;
use App\Task\Command\UpdateTaskName\UpdateTaskNameCommand;
use App\Task\Domain\Task;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка обновления имени задачи
 */
#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/update-task-name', methods: ['POST'])]
#[AsController]
final class UpdateTaskNameAction
{
    public function __construct(
        private readonly UpdateTaskName $updateTaskName,
        private readonly Flush $flush,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): SuccessResponse
    {
        try {
            ($this->updateTaskName)($task, $command);

            ($this->flush)();

            $this->logger->info('Задача обновлена', [
                'id' => $task->getTaskId(),
                self::class => __FUNCTION__,
            ]);
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
