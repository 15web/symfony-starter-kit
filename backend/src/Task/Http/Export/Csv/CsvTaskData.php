<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Csv;

use Symfony\Component\Uid\Uuid;

final class CsvTaskData
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $taskName,
        public readonly bool $isCompleted,
    ) {
    }
}
