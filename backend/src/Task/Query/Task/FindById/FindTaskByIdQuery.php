<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindById;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос на нахождение задачи по айди
 */
final readonly class FindTaskByIdQuery
{
    public function __construct(
        public Uuid $taskId,
        public Uuid $userId,
    ) {
    }
}
