<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\Task\Command\CompleteTask;
use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use App\User\User\Http\UserIdArgumentValueResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка завершения задачи
 */
#[IsGranted(UserRole::User)]
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
        Task $task,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId
    ): ApiObjectResponse {
        if (!$userId->equalTo($task->getUserId())) {
            throw new ApiNotFoundException(['Запись не найдена']);
        }

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

        return new ApiObjectResponse(
            data: new SuccessResponse()
        );
    }
}
