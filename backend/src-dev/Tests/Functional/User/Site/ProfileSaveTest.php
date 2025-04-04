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
#[TestDox('Сохранение профиля')]
final class ProfileSaveTest extends ApiWebTestCase
{
    private const string PHONE_NUMBER = '89272222222';

    #[TestDox('Профиль сохранен')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = Profile::save(
            name: $name = 'Имя 1',
            phone: self::PHONE_NUMBER,
            token: $token,
        );

        self::assertSuccessResponse($response);
        $response = Profile::info($token);
        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{phone: ?string, name: ?string}
         * } $profileInfo */
        $profileInfo = self::jsonDecode($response->getContent());

        self::assertSame(self::PHONE_NUMBER, $profileInfo['data']['phone']);
        self::assertSame($name, $profileInfo['data']['name']);
    }

    #[TestDox('Профиль сохранен 2 раза, данные изменились')]
    public function testReSave(): void
    {
        $token = User::auth();

        Profile::save(
            name: $name1 = 'Имя 1',
            phone: self::PHONE_NUMBER,
            token: $token,
        );
        Profile::save(
            name: $name2 = 'Имя 2',
            phone: $phone = '89272222221',
            token: $token,
        );

        $response = Profile::info($token);

        /** @var array{
         *     data: array{phone: ?string, name: ?string}
         * } $profileInfo */
        $profileInfo = self::jsonDecode($response->getContent());
        self::assertSuccessResponse($response);

        self::assertSame($phone, $profileInfo['data']['phone']);
        self::assertSame($name2, $profileInfo['data']['name']);

        self::assertNotSame(self::PHONE_NUMBER, $profileInfo['data']['phone']);
        self::assertNotSame($name1, $profileInfo['data']['name']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = Profile::save(
            name: 'Тестовый профиль',
            phone: self::PHONE_NUMBER,
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Администратору разрешен доступ')]
    public function testAdminAccess(): void
    {
        $adminToken = User::auth('admin@example.test');

        $response = Profile::save(
            name: 'Тестовый профиль',
            phone: self::PHONE_NUMBER,
            token: $adminToken,
        );

        self::assertSuccessContentResponse($response);
    }

    #[TestDox('Неверный запрос')]
    public function testBadRequests(): void
    {
        $token = User::auth();

        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['name' => '', 'phone' => ''], $token);

        $badJson = '{"name"=1,"phone"=89272222222}';
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/profile',
            body: $badJson,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    /**
     * @param array<mixed, string> $body
     */
    private function assertBadRequests(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/profile',
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }
}
