<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use App\Infrastructure\AsService;
use Doctrine\DBAL\Connection;

/**
 * Возвращает кол-во всех записей
 */
#[AsService]
final readonly class CountAllTasksByUserId
{
    public function __construct(
        private Connection $connection,
        private Filter $filter
    ) {
    }

    public function __invoke(FindAllTasksByUserIdQuery $query): int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(t.id)'])
            ->from('task', 't');

        $this->filter->applyFilter($queryBuilder, $query);

        /** @var int $result */
        $result = $queryBuilder->executeQuery()->fetchOne();

        return $result;
    }
}
