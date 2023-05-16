<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use Webmozart\Assert\Assert;

/**
 * Команда обновления имени задачи
 */
final readonly class UpdateTaskNameCommand
{
    public function __construct(public string $taskName)
    {
        Assert::notEmpty($taskName, 'Укажите наименование задачи');
    }
}
