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
        private Connection $connection,
        private Filter $filter
    ) {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(FindAllTasksByUserIdQuery $query): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
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
            /** @var string $id */
            $id = $item['id'];

            /** @var string $createdAt */
            $createdAt = $item['createdAt'];

            /** @var string $taskName */
            $taskName = $item['taskName'];

            /** @var bool $isCompleted */
            $isCompleted = (bool) $item['isCompleted'];

            /** @var DateTimeImmutable $date */
            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt);

            $tasks[] = new TaskData(
                Uuid::fromString($id),
                $taskName,
                $isCompleted,
                $date
            );
        }

        return $tasks;
    }
}
