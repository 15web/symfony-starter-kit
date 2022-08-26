<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindUncompletedTasksByUserId;

use Symfony\Component\Uid\Uuid;

final class FindUncompletedTasksByUserIdQuery
{
    public function __construct(public readonly Uuid $userId)
    {
    }
}
