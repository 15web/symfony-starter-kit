<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\AsService;
use App\Task\Domain\Task;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class RemoveTask
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
