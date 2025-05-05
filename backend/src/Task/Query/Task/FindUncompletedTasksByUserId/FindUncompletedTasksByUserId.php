<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindUncompletedTasksByUserId;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения незавершенных задач по пользователю
 */
final readonly class FindUncompletedTasksByUserId
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @return TaskData[]
     */
    public function __invoke(FindUncompletedTasksByUserIdQuery $query): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\Task\Query\Task\FindUncompletedTasksByUserId\TaskData(t.taskName.value, t.createdAt)
                FROM App\Task\Domain\Task AS t
                WHERE t.userId = :userId AND t.isCompleted = false
                ORDER BY t.createdAt DESC
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userId', $query->userId);

        /** @var TaskData[] $taskData */
        $taskData = $dqlQuery->getResult();

        return $taskData;
    }
}
