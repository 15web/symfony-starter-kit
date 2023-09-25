<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use DateTimeImmutable;

/**
 * Данные для списка настроек
 */
final readonly class SettingListData
{
    public function __construct(
        public string $type,
        public string $value,
        public bool $isPublic,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
    ) {}
}
