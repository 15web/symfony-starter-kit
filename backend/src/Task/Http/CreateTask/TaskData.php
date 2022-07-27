<?php

declare(strict_types=1);

namespace App\Task\Http\CreateTask;

use Symfony\Component\Uid\Uuid;

final class TaskData
{
    public function __construct(public readonly Uuid $id)
    {
    }
}
