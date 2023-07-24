<?php

declare(strict_types=1);

namespace App\Tests\Functional\Setting;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Setting;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @internal
 */
#[TestDox('Список публичных настроек')]
final class SettingListTest extends ApiWebTestCase
{
    #[TestDox('Получение списка')]
    public function testSuccess(): void
    {
        $filteredSettings = array_filter(
            Setting::list(),
            static fn ($setting): bool => $setting['isPublic']
        );

        $publicSettings = Setting::publicList();

        self::assertCount(\count($filteredSettings), $publicSettings);

        foreach ($publicSettings as $publicSetting) {
            self::assertNotEmpty($publicSetting['type']);
            self::assertNotEmpty($publicSetting['value']);
        }
    }
}
