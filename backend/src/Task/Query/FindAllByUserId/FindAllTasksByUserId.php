<?php

declare(strict_types=1);

namespace App\Task\Query\FindAllByUserId;

use Doctrine\ORM\EntityManagerInterface;

final class FindAllTasksByUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(FindAllTasksByUserIdQuery $query): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\Task\Query\FindAllByUserId\TaskData(t.id, t.taskName.value, t.taskCompleted.isCompleted, t.createdAt)
                FROM App\Task\Domain\Task AS t
                WHERE t.userId = :userId
                ORDER BY t.taskCompleted.isCompleted, t.createdAt DESC
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userId', $query->userId->toBinary());

        return $dqlQuery->getResult();
    }
}
