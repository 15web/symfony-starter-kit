<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use App\User\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class CreateTask
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, TaskId $taskId, UserId $userId): void
    {
        $task = new Task($taskId, new TaskName($createTaskCommand->taskName), $userId);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
