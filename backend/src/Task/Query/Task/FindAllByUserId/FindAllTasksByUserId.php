<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use App\Infrastructure\AsService;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

/**
 * Возвращает записи с limit и offset
 */
#[AsService]
final readonly class FindAllTasksByUserId
{
    public function __construct(
        private Connection $entityManager,
        private Filter $filter
    ) {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(FindAllTasksByUserIdQuery $query): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(['t.id', 't.task_name_value AS taskName', 't.is_completed AS isCompleted', 't.created_at AS createdAt'])
            ->from('task', 't')
            ->orderBy('t.is_completed', 'DESC')
            ->addOrderBy('t.created_at', 'DESC');

        $this->filter->applyFilter($queryBuilder, $query);

        $queryBuilder
            ->setFirstResult($query->offset)
            ->setMaxResults($query->limit);

        $items = $queryBuilder->executeQuery()->fetchAllAssociative();

        $tasks = [];
        foreach ($items as $item) {
            $tasks[] = new TaskData(
                Uuid::fromString($item['id']),
                $item['taskName'],
                (bool) $item['isCompleted'],
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $item['createdAt'])
            );
        }

        return $tasks;
    }
}
