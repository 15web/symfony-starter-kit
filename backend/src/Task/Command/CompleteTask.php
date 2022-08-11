<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\AsService;
use App\Task\Domain\Task;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class CompleteTask
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \App\Task\Domain\TaskAlreadyIsDoneException
     */
    public function __invoke(Task $task): void
    {
        $task->markAsDone();

        $this->entityManager->flush();
    }
}
