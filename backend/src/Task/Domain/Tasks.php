<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\AsService;
use App\User\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class Tasks
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findByIdAndUserId(TaskId $taskId, UserId $userId): ?Task
    {
        return $this->entityManager->getRepository(Task::class)->findOneBy([
            'id' => $taskId->value,
            'userId' => $userId->value,
        ]);
    }

    public function add(Task $task): void
    {
        $this->entityManager->persist($task);
    }

    public function remove(Task $task): void
    {
        $this->entityManager->remove($task);
    }
}
