<?php

declare(strict_types=1);

namespace App\Setting\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Setting\Domain\SettingType;
use Webmozart\Assert\Assert;

/**
 * Команда сохранения настройки
 */
#[ApiRequest]
final readonly class SaveSettingCommand
{
    public function __construct(
        public string $type,
        public string $value,
    ) {
        Assert::notEmpty($type, 'Укажите тип');
        Assert::notEmpty($value, 'Укажите значение');
        Assert::inArray($type, array_column(SettingType::cases(), 'value'), 'Указан неверный тип');
    }
}
