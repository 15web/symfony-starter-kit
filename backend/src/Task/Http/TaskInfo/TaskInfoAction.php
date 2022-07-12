<?php

declare(strict_types=1);

namespace App\Task\Http\TaskInfo;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Model\Task;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}', methods: ['GET'])]
#[ParamConverter('task', Task::class)]
final class TaskInfoAction
{
    public function __invoke(Task $task, #[CurrentUser] ?User $user): TaskData
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user->getId()->equals($task->getUserId()) === false) {
            throw new ApiNotFoundException();
        }

        return new TaskData(
            $task->getId(),
            $task->getTaskName()->getValue(),
            $task->isCompleted(),
            $task->getCreatedAt(),
            $task->getCompletedAt(),
            $task->getUpdatedAt(),
        );
    }
}
