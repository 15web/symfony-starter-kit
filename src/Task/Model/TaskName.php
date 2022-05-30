<?php

declare(strict_types=1);

namespace App\Task\Model;

use Webmozart\Assert\Assert;

final class TaskName
{
    public function __construct(private readonly string $value)
    {
        Assert::notEmpty($value, 'Укажите наименование задачи');
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
