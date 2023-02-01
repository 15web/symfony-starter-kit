<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindById;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос на нахождение задачи по айди
 */
final class FindTaskByIdQuery
{
    public function __construct(
        public readonly Uuid $taskId,
        public readonly Uuid $userId,
    ) {
    }
}
