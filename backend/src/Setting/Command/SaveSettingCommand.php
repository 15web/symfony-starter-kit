<?php

declare(strict_types=1);

namespace App\Setting\Command;

use App\Setting\Domain\SettingType;

/**
 * Команда сохранения настройки
 */
final readonly class SaveSettingCommand
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        public SettingType $type,
        public string $value,
    ) {}
}
