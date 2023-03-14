<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Pagination\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\TaskData;

/**
 * Ответ списка задач с пагинацией
 */
final readonly class TaskListResponse
{
    /**
     * @param TaskData[] $data
     */
    public function __construct(
        public array $data,
        public PaginationResponse $pagination
    ) {
    }
}
