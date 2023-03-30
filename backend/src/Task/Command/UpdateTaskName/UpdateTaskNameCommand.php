<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда обновления имени задачи
 */
#[ApiRequest]
final readonly class UpdateTaskNameCommand
{
    public function __construct(public string $taskName)
    {
        Assert::notEmpty($taskName, 'Укажите наименование задачи');
    }
}
