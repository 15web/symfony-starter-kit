<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Запрос на нахождение всех задач по пользователю
 */
final class FindAllTasksByUserIdQuery
{
    public function __construct(
        public readonly Uuid $userId,
        public readonly int $limit = 10,
        public readonly int $offset = 0,
    ) {
        Assert::positiveInteger($limit);
        Assert::greaterThanEq($offset, 0);
    }
}
