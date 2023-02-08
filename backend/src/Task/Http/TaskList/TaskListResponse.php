<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\Pagination\PaginationResponse;
use App\Task\Query\Task\FindAllByUserId\TaskData;

final class TaskListResponse
{
    /**
     * @param TaskData[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationResponse $pagination
    ) {
    }
}
