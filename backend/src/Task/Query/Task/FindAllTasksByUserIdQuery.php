<?php

declare(strict_types=1);

namespace App\Task\Query\Task;

use Symfony\Component\Uid\Uuid;

final class FindAllTasksByUserIdQuery
{
    public function __construct(public readonly Uuid $userId)
    {
    }
}
