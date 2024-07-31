<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Task\Query\Task\FindById\FindTaskById;
use App\Task\Query\Task\FindById\FindTaskByIdQuery;
use App\Task\Query\Task\FindById\TaskData;
use App\Task\Query\Task\FindById\TaskNotFoundException;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка информации задачи
 */
#[IsGranted(UserRole::User)]
#[Route('/tasks/{id}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskInfoAction
{
    public function __construct(private FindTaskById $findTaskById) {}

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)]
        Uuid $id,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ApiObjectResponse {
        return new ApiObjectResponse(
            data: $this->buildResponseData($id, $userId),
        );
    }

    /**
     * @throws ApiNotFoundException
     */
    private function buildResponseData(Uuid $id, UserId $userId): TaskData
    {
        try {
            $taskData = ($this->findTaskById)(
                query: new FindTaskByIdQuery(
                    taskId: $id,
                    userId: $userId->value,
                ),
            );
        } catch (TaskNotFoundException) {
            throw new ApiNotFoundException(['Задача не найдена']);
        }

        return $taskData;
    }
}
