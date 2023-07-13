<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Pagination\PaginationRequest;
use App\Infrastructure\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Pagination\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\CountAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка списка задач
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskListAction
{
    public function __construct(
        private CountAllTasksByUserId $countAllTasksByUserId,
        private FindAllTasksByUserId $findAllTasksByUserId,
    ) {
    }

    public function __invoke(
        #[ValueResolver(UserIdArgumentValueResolver::class)] UserId $userId,
        #[ValueResolver(PaginationRequestArgumentResolver::class)] PaginationRequest $paginationRequest,
    ): TaskListResponse {
        $query = new FindAllTasksByUserIdQuery(
            $userId->value,
            $paginationRequest->limit,
            $paginationRequest->offset,
        );

        $tasks = ($this->findAllTasksByUserId)($query);
        $allTasksCount = ($this->countAllTasksByUserId)($query);

        $pagination = new PaginationResponse(
            total: $allTasksCount
        );

        return new TaskListResponse($tasks, $pagination);
    }
}
