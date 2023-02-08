<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Хендлер нахождения всех задач по пользователю
 */
#[AsService]
final class FindAllTasksByUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return TaskData[]
     */
    public function execute(FindAllTasksByUserIdQuery $query): array
    {
        $dql = <<<DQL
                SELECT
                NEW App\Task\Query\Task\FindAllByUserId\TaskData(t.id, t.taskName.value, t.isCompleted, t.createdAt)
                FROM App\Task\Domain\Task AS t
                WHERE 1 = 1 {$this->applyFilter()}
                ORDER BY t.isCompleted, t.createdAt DESC
            DQL;

        $ormQuery = $this->entityManager->createQuery($dql);
        $this->applyParameters($ormQuery, $query);

        $ormQuery
            ->setFirstResult($query->offset)
            ->setMaxResults($query->limit);

        /** @var TaskData[] $tasks */
        $tasks = $ormQuery->getResult();

        return $tasks;
    }

    public function countAll(FindAllTasksByUserIdQuery $query): int
    {
        $dql = <<<DQL
                SELECT
                COUNT(t.id)
                FROM App\Task\Domain\Task AS t
                WHERE 1 = 1 {$this->applyFilter()}
            DQL;

        $ormQuery = $this->entityManager->createQuery($dql);
        $this->applyParameters($ormQuery, $query);

        /** @var int $result */
        $result = $ormQuery->getSingleScalarResult();

        return $result;
    }

    private function applyFilter(): string
    {
        $conditions = [];
        $conditions[] = 'AND t.userId = :userId';

        return implode(' ', $conditions);
    }

    private function applyParameters(Query $ormQuery, FindAllTasksByUserIdQuery $query): void
    {
        $ormQuery->setParameter('userId', $query->userId->toBinary());
    }
}
