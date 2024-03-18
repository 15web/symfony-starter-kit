<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

/**
 * Мета информация для списка задач
 */
final class TaskListMetaData
{
    public function __construct(
        public int $incompletedTasksCount
    ) {}
}
