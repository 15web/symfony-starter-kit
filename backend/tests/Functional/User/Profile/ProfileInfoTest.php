<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\Profile;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Profile;
use App\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Получение информации по профилю')]
final class ProfileInfoTest extends ApiWebTestCase
{
    private const PHONE_NUMBER = '89272222222';

    #[TestDox('Создан профиль, получена информация по нему')]
    public function testSuccess(): void
    {
        $token = User::auth();

        Profile::save(
            $profileName = 'Тестовое имя 1',
            $profilePhone = self::PHONE_NUMBER,
            $token
        );

        $response = self::request(Request::METHOD_GET, '/api/profile', token: $token);
        self::assertSuccessResponse($response);

        $profile = self::jsonDecode($response->getContent());

        self::assertSame($profilePhone, $profile['phone']);
        self::assertSame($profileName, $profile['name']);
    }

    #[TestDox('Пустые данные профиля')]
    public function testEmpty(): void
    {
        $token = User::auth();

        $response = self::request(Request::METHOD_GET, '/api/profile', token: $token);
        self::assertSuccessResponse($response);

        $profile = self::jsonDecode($response->getContent());

        self::assertNull($profile['phone']);
        self::assertNull($profile['name']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        Profile::save('Имя 1', self::PHONE_NUMBER, $token);

        $response = self::request(Request::METHOD_GET, '/api/profile', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
