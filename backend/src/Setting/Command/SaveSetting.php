<?php

declare(strict_types=1);

namespace App\Setting\Command;

use App\Infrastructure\AsService;
use App\Setting\Domain\Setting;
use App\Setting\Domain\SettingNotFoundException;
use App\Setting\Domain\SettingsRepository;

/**
 * Хендлер сохранения настройки
 */
#[AsService]
final readonly class SaveSetting
{
    public function __construct(private SettingsRepository $settingsRepository) {}

    /**
     * @throws SettingNotFoundException
     */
    public function __invoke(SaveSettingCommand $command): Setting
    {
        $setting = $this->settingsRepository->getByType($command->type);

        $setting->change($command->value);

        return $setting;
    }
}
