<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Export\Xml;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Данные задачи для xml формата
 */
final readonly class XmlTaskData
{
    public function __construct(
        public Uuid $id,
        public DateTimeImmutable $createdAt,
        public string $taskName,
        public bool $isCompleted,
    ) {}
}
