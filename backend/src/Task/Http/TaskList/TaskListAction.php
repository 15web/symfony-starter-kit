<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Pagination\PaginationRequest;
use App\Infrastructure\Pagination\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка списка задач
 */
#[IsGranted('ROLE_USER')]
#[Route('/tasks', methods: ['GET'])]
#[AsController]
final readonly class TaskListAction
{
    public function __construct(private FindAllTasksByUserId $findAllTasksByUserId)
    {
    }

    public function __invoke(UserId $userId, PaginationRequest $paginationRequest): TaskListResponse
    {
        $query = new FindAllTasksByUserIdQuery(
            $userId->value,
            $paginationRequest->perPage,
            $paginationRequest->getOffset(),
        );

        $tasks = $this->findAllTasksByUserId->execute($query);
        $allTasksCount = $this->findAllTasksByUserId->countAll($query);

        $pagination = new PaginationResponse(
            total: $allTasksCount,
            perPage: $paginationRequest->perPage,
            currentPage: $paginationRequest->page
        );

        return new TaskListResponse($tasks, $pagination);
    }
}
