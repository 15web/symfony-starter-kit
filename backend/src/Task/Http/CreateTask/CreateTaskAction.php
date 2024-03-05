<?php

declare(strict_types=1);

namespace App\Task\Http\CreateTask;

use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\CreateTask\CreateTaskCommand;
use App\Task\Domain\TaskId;
use App\User\SignIn\Http\Auth\IsGranted;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка создания задачи
 */
#[IsGranted(UserRole::User)]
#[Route('/tasks', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class CreateTaskAction
{
    public function __construct(
        private CreateTask $createTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        CreateTaskCommand $createTaskCommand,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ApiObjectResponse {
        $taskId = new TaskId();
        ($this->createTask)(
            createTaskCommand: $createTaskCommand,
            taskId: $taskId,
            userId: $userId,
        );

        ($this->flush)();

        $this->logger->info('Задача создана', [
            'id' => $taskId,
            self::class => __FUNCTION__,
        ]);

        return new ApiObjectResponse(
            data: new TaskData($taskId->value)
        );
    }
}
