<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Profile;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Получение информации по профилю')]
final class ProfileInfoTest extends ApiWebTestCase
{
    private const string PHONE_NUMBER = '89272222222';

    #[TestDox('Создан профиль, получена информация по нему')]
    public function testSuccess(): void
    {
        $token = User::auth();

        Profile::save(
            name: $profileName = 'Тестовое имя 1',
            phone: $profilePhone = self::PHONE_NUMBER,
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/profile',
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{phone: string, name: string}
         * } $profile */
        $profile = self::jsonDecode($response->getContent());

        self::assertSame($profilePhone, $profile['data']['phone']);
        self::assertSame($profileName, $profile['data']['name']);
    }

    #[TestDox('Пустые данные профиля')]
    public function testEmpty(): void
    {
        $token = User::auth();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/profile',
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{phone: ?string, name: ?string}
         * } $profile */
        $profile = self::jsonDecode($response->getContent());

        self::assertNull($profile['data']['phone']);
        self::assertNull($profile['data']['name']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        Profile::save(
            name: 'Имя 1',
            phone: self::PHONE_NUMBER,
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/profile',
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Администратору разрешен доступ')]
    public function testAdminAccess(): void
    {
        $userToken = User::auth();

        Profile::save(
            name: 'Имя 1',
            phone: self::PHONE_NUMBER,
            token: $userToken,
        );

        $adminToken = User::auth('admin@example.test');

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/profile',
            token: $adminToken,
        );

        self::assertSuccessContentResponse($response);
    }
}
