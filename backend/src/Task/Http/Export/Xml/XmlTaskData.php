<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Xml;

use Symfony\Component\Uid\Uuid;

/**
 * Данные задачи для xml формата
 */
final class XmlTaskData
{
    public function __construct(
        public readonly Uuid $id,
        public readonly \DateTimeImmutable $createdAt,
        public readonly string $taskName,
        public readonly bool $isCompleted,
    ) {
    }
}
