<?php

declare(strict_types=1);

namespace App\Setting\Http\SettingList;

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
