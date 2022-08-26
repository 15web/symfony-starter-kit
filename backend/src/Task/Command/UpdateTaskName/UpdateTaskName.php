<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\TaskName;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class UpdateTaskName
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): void
    {
        $task->changeTaskName(new TaskName($command->taskName));

        $this->entityManager->flush();
    }
}
