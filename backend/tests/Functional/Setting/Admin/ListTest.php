<?php

declare(strict_types=1);

namespace App\Tests\Functional\Setting\Admin;

use App\Setting\Domain\SettingType;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Setting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @internal
 */
#[TestDox('Список публичных настроек')]
final class ListTest extends ApiWebTestCase
{
    #[TestDox('Получение списка')]
    public function testSuccess(): void
    {
        $settings = Setting::list();
        self::assertCount(\count(SettingType::cases()), $settings);

        foreach ($settings as $setting) {
            self::assertNotEmpty($setting['type']);
            self::assertNotEmpty($setting['value']);
            self::assertIsBool($setting['isPublic']);
        }
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request('GET', '/api/admin/settings', token: $notValidToken);
        self::assertAccessDenied($response);
    }
}
