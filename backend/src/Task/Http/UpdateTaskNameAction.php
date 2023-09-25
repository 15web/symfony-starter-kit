<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\UpdateTaskName\UpdateTaskName;
use App\Task\Command\UpdateTaskName\UpdateTaskNameCommand;
use App\Task\Domain\Task;
use App\User\SignUp\Domain\UserRole;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка обновления имени задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/update-task-name', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class UpdateTaskNameAction
{
    public function __construct(
        private UpdateTaskName $updateTaskName,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(TaskArgumentValueResolver::class)]
        Task $task,
        #[ValueResolver(ApiRequestValueResolver::class)]
        UpdateTaskNameCommand $command,
    ): SuccessResponse {
        try {
            ($this->updateTaskName)(
                task: $task,
                command: $command,
            );

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
