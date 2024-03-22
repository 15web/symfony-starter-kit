<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий задач
 */
#[AsService]
final readonly class TaskRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function findById(TaskId $taskId): ?Task
    {
        return $this->entityManager->getRepository(Task::class)->findOneBy([
            'id' => $taskId->value,
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
