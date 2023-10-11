<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

/**
 * Команда создания задачи
 */
final readonly class CreateTaskCommand
{
    /**
     * @param non-empty-string $taskName
     */
    public function __construct(public string $taskName) {}
}
