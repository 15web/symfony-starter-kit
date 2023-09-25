<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use App\Infrastructure\AsService;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Возвращает записи с limit и offset
 */
#[AsService]
final readonly class FindAllTasksByUserId
{
    public function __construct(
        private Connection $connection,
        private Filter $filter,
        private DenormalizerInterface $denormalizer,
    ) {}

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

        /** @var TaskData[] $tasks */
        $tasks = $this->denormalizer->denormalize(
            data: $items,
            type: TaskData::class.'[]',
            context: [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ],
        );

        return $tasks;
    }
}
