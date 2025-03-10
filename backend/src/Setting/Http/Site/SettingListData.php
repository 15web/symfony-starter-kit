<?php

declare(strict_types=1);

namespace App\Setting\Http\Site;

/**
 * Данные для списка настроек
 */
final readonly class SettingListData
{
    public function __construct(
        public string $type,
        public string $value,
    ) {}
}
