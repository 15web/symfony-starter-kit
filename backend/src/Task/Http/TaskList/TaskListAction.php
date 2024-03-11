<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationRequest;
use App\Infrastructure\Response\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Response\Pagination\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\CountAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
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

        $pagination = new PaginationResponse($allTasksCount);

        return new ApiListObjectResponse(
            data: $tasks,
            pagination: $pagination,
        );
    }
}
