<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Setting\Admin;

use App\Setting\Domain\SettingType;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Setting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Список всех настроек')]
final class ListTest extends ApiWebTestCase
{
    #[TestDox('Получение списка')]
    public function testSuccess(): void
    {
        $settings = Setting::adminList();
        self::assertCount(\count(SettingType::cases()), $settings);

        foreach ($settings as $setting) {
            self::assertNotEmpty($setting['type']);
            self::assertNotEmpty($setting['value']);
            self::assertArrayHasKey('isPublic', $setting);
        }
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request(Request::METHOD_GET, '/api/admin/settings', token: $notValidToken);
        self::assertAccessDenied($response);
    }
}
