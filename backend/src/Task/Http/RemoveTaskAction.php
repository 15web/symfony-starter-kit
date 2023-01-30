<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Domain\Task;
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
    public function __construct(private readonly RemoveTask $removeTask)
    {
    }

    public function __invoke(Task $task): SuccessResponse
    {
        ($this->removeTask)($task);

        return new SuccessResponse();
    }
}
