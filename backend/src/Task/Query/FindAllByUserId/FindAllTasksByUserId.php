<?php

declare(strict_types=1);

namespace App\Task\Query\FindAllByUserId;

use App\AsService;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
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
                NEW App\Task\Query\FindAllByUserId\TaskData(t.id, t.taskName.value, t.isCompleted, t.createdAt)
                FROM App\Task\Domain\Task AS t
                WHERE t.userId = :userId
                ORDER BY t.isCompleted, t.createdAt DESC
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userId', $query->userId->toBinary());

        return $dqlQuery->getResult();
    }
}
