<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Общий фильтр
 */
final readonly class Filter
{
    public function applyFilter(QueryBuilder $queryBuilder, FindAllTasksByUserIdQuery $query): void
    {
        $queryBuilder
            ->andWhere('t.user_id = :user_id')
            ->setParameter('user_id', $query->userId);
    }
}
