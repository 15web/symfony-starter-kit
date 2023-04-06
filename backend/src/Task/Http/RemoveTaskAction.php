<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Domain\Task;
use App\User\SignUp\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка удаления задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/remove', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class RemoveTaskAction
{
    public function __construct(
        private RemoveTask $removeTask,
        private Flush $flush,
        private LoggerInterface $logger,
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
