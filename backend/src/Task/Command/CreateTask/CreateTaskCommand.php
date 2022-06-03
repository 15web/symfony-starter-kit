<?php

declare(strict_types=1);

namespace App\Task\Command\CreateTask;

use App\Infrastructure\ArgumentValueResolver\ApiRequest;
use Webmozart\Assert\Assert;

final class CreateTaskCommand implements ApiRequest
{
    public function __construct(public readonly string $taskName)
    {
        Assert::notEmpty($taskName);
    }
}
