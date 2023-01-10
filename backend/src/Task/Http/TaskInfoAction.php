<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Task\Query\Task\FindById\FindTaskById;
use App\Task\Query\Task\FindById\FindTaskByIdQuery;
use App\Task\Query\Task\FindById\TaskData;
use App\Task\Query\Task\FindById\TaskNotFoundException;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{taskId}', methods: ['GET'])]
#[AsController]
final class TaskInfoAction
{
    public function __construct(private readonly FindTaskById $findTaskById)
    {
    }

    public function __invoke(Uuid $taskId, UserId $userId): TaskData
    {
        try {
            $taskData = ($this->findTaskById)(new FindTaskByIdQuery($taskId, $userId->value));
        } catch (TaskNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return $taskData;
    }
}
