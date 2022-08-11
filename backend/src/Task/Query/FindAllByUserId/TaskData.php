<?php

declare(strict_types=1);

namespace App\Task\Query\FindAllByUserId;

use Symfony\Component\Uid\Uuid;

final class TaskData
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $taskName,
        public readonly bool $isCompleted,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }
}
