<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindUncompletedTasksByUserId;

use App\AsService;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class FindUncompletedTasksByUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

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
        $dqlQuery->setParameter('userId', $query->userId->toBinary());

        return $dqlQuery->getResult();
    }
}
