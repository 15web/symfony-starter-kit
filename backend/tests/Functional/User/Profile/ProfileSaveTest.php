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
#[TestDox('Сохранение профиля')]
final class ProfileSaveTest extends ApiWebTestCase
{
    private const PHONE_NUMBER = '89272222222';

    #[TestDox('Профиль сохранен')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = Profile::save($name = 'Имя 1', self::PHONE_NUMBER, $token);

        self::assertSuccessResponse($response);
        $response = Profile::info($token);
        self::assertSuccessResponse($response);

        $profileInfo = self::jsonDecode($response->getContent());

        self::assertSame(self::PHONE_NUMBER, $profileInfo['phone']);
        self::assertSame($name, $profileInfo['name']);
    }

    #[TestDox('Профиль сохранен 2 раза, данные изменились')]
    public function testReSave(): void
    {
        $token = User::auth();

        Profile::save($name1 = 'Имя 1', self::PHONE_NUMBER, $token);
        Profile::save($name2 = 'Имя 2', $phone = '89272222221', $token);

        $response = Profile::info($token);
        $profileInfo = self::jsonDecode($response->getContent());
        self::assertSuccessResponse($response);

        self::assertSame($phone, $profileInfo['phone']);
        self::assertSame($name2, $profileInfo['name']);

        self::assertNotSame(self::PHONE_NUMBER, $profileInfo['phone']);
        self::assertNotSame($name1, $profileInfo['name']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = Profile::save('Тестовый профиль', self::PHONE_NUMBER, $notValidToken);

        self::assertAccessDenied($response);
    }

    #[TestDox('Неверный запрос')]
    public function testBadRequests(): void
    {
        $token = User::auth();

        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['name' => '', 'phone' => ''], $token);

        $badJson = '{"name"=1,"phone"=89272222222}';
        $response = self::request(Request::METHOD_POST, '/api/profile-save', $badJson, token: $token, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }

    private function assertBadRequests(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/profile-save', $body, token: $token, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }
}
