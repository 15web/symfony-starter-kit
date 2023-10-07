<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CompleteTask;
use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use App\User\SignUp\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка завершения задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/complete', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class CompleteTaskAction
{
    public function __construct(
        private CompleteTask $completeTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(TaskArgumentValueResolver::class)]
        Task $task
    ): SuccessResponse {
        try {
            ($this->completeTask)($task);

            ($this->flush)();

            $this->logger->info('Задача завершена', [
                'id' => $task->getTaskId(),
                self::class => __FUNCTION__,
            ]);
        } catch (TaskAlreadyIsDoneException) {
            throw new ApiBadRequestException(['Задача уже выполнена']);
        }

        return new SuccessResponse();
    }
}
