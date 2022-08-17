<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class CreateTask
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, TaskId $taskId, Uuid $userId): void
    {
        $task = new Task($taskId, new TaskName($createTaskCommand->taskName), $userId);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
