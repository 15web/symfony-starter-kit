<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Export\Csv;

use Symfony\Component\Uid\Uuid;

/**
 * Данные задачи для csv формата
 */
final readonly class CsvTaskData
{
    public function __construct(
        public Uuid $id,
        public string $taskName,
        public bool $isCompleted,
    ) {}
}
