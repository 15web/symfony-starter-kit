<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

/**
 * Команда обновления имени задачи
 */
final readonly class UpdateTaskNameCommand
{
    /**
     * @param non-empty-string $taskName
     */
    public function __construct(public string $taskName) {}
}
