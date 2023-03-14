<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindAllByUserId;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Запрос на нахождение всех задач по пользователю
 */
final readonly class FindAllTasksByUserIdQuery
{
    public function __construct(
        public Uuid $userId,
        public int $limit = 10,
        public int $offset = 0,
    ) {
        Assert::positiveInteger($limit);
        Assert::greaterThanEq($offset, 0);
    }
}
