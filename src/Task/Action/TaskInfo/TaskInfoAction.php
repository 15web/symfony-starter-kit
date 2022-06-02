<?php

declare(strict_types=1);

namespace App\Task\Action\TaskInfo;

use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks/{id}', methods: ['GET'])]
#[ParamConverter('task', Task::class)]
final class TaskInfoAction
{
    public function __invoke(Task $task): TaskData
    {
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
