<?php

declare(strict_types=1);

namespace App\Task\Query\FindUncompletedTasksByUserId;

use Symfony\Component\Uid\Uuid;

final class FindUncompletedTasksByUserIdQuery
{
    public function __construct(public readonly Uuid $userId)
    {
    }
}
