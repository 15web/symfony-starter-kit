<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Request\Pagination\PaginationRequest;
use App\Infrastructure\Request\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\CountAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка задач
 */
#[IsGranted(UserRole::User)]
#[Route('/tasks', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskListAction
{
    public function __construct(
        private CountAllTasksByUserId $countAllTasksByUserId,
        private FindAllTasksByUserId $findAllTasksByUserId,
    ) {}

    public function __invoke(
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
        #[ValueResolver(PaginationRequestArgumentResolver::class)]
        PaginationRequest $paginationRequest,
    ): ApiListObjectResponse {
        $query = new FindAllTasksByUserIdQuery(
            userId: $userId->value,
            limit: $paginationRequest->limit,
            offset: $paginationRequest->offset,
        );

        $tasks = ($this->findAllTasksByUserId)($query);
        $allTasksCount = ($this->countAllTasksByUserId)($query);

        $uncompletedTasksCount = \count(array_filter($tasks, static fn (TaskData $task): bool => $task->isCompleted === false));

        $pagination = new PaginationResponse($allTasksCount);

        return new ApiListObjectResponse(
            data: $tasks,
            pagination: $pagination,
            meta: new TaskListMetaData(uncompletedTasksCount: $uncompletedTasksCount),
        );
    }
}
