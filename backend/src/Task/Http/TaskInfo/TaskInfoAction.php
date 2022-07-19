<?php

declare(strict_types=1);

namespace App\Task\Http\TaskInfo;

use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}', methods: ['GET'])]
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
