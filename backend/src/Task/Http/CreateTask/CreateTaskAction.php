<?php

declare(strict_types=1);

namespace App\Task\Http\CreateTask;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\Flush;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\CreateTask\CreateTaskCommand;
use App\Task\Domain\TaskId;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка создания задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/create', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class CreateTaskAction
{
    public function __construct(
        private CreateTask $createTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, UserId $userId): TaskData
    {
        try {
            $taskId = new TaskId();
            ($this->createTask)($createTaskCommand, $taskId, $userId);

            ($this->flush)();

            $this->logger->info('Задача создана', [
                'id' => $taskId,
                self::class => __FUNCTION__,
            ]);
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new TaskData($taskId->value);
    }
}
