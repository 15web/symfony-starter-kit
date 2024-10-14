<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Domain\Task;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка удаления задачи
 */
#[IsGranted(UserRole::User)]
#[Route('/tasks/{id}', methods: [Request::METHOD_DELETE])]
#[AsController]
final readonly class RemoveTaskAction
{
    public function __construct(
        private RemoveTask $removeTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(TaskArgumentValueResolver::class)]
        Task $task,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ApiObjectResponse {
        if (!$userId->equalTo($task->getUserId())) {
            throw new ApiNotFoundException(['Запись не найдена']);
        }

        ($this->removeTask)($task);

        ($this->flush)();

        $this->logger->info('Задача удалена', [
            'id' => $task->getTaskId(),
            self::class => __FUNCTION__,
        ]);

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
