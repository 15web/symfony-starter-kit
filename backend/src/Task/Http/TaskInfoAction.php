<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Task\Query\Task\FindById\FindTaskById;
use App\Task\Query\Task\FindById\FindTaskByIdQuery;
use App\Task\Query\Task\FindById\TaskData;
use App\Task\Query\Task\FindById\TaskNotFoundException;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка информации задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskInfoAction
{
    public function __construct(private FindTaskById $findTaskById)
    {
    }

    public function __invoke(Uuid $id, UserId $userId): TaskData
    {
        try {
            $taskData = ($this->findTaskById)(new FindTaskByIdQuery($id, $userId->value));
        } catch (TaskNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return $taskData;
    }
}
