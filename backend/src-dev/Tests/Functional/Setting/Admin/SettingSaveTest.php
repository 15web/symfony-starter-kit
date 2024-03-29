<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Setting\Admin;

use App\Setting\Domain\SettingType;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Сохранение настройки')]
final class SettingSaveTest extends ApiWebTestCase
{
    #[TestDox('Настройка сохранена')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $body = [];
        $body['type'] = SettingType::EMAIL_SITE;
        $body['value'] = 'symfony-starter-kit-test';
        $bodyJson = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/settings', $bodyJson, token: $token);

        self::assertSuccessResponse($response);

        $responseList = self::request(Request::METHOD_GET, '/api/admin/settings', token: $token);
        self::assertSuccessResponse($responseList);

        /** @var array{
         *     data: array<int, array{
         *          id: string,
         *          type: string,
         *          value: string,
         *          isPublic: bool,
         *          createdAt: string,
         *          updatedAt: string|null,
         *     }>,
         *     pagination: array{total: int},
         * } $settings
         */
        $settings = self::jsonDecode($responseList->getContent());

        foreach ($settings['data'] as $setting) {
            if ($setting['type'] !== SettingType::EMAIL_SITE->value) {
                continue;
            }

            self::assertSame($body['value'], $setting['value']);
        }
    }

    #[TestDox('Настройка не найдена в enum')]
    public function testNotFound(): void
    {
        $token = User::auth();

        $body = [];
        $body['type'] = 'not-found-type';
        $body['value'] = 'symfony-starter-kit-test';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/settings', $body, token: $token);
        self::assertBadRequest($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $body = [];
        $body['type'] = SettingType::EMAIL_SITE;
        $body['value'] = 'symfony-starter-kit-test';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/settings', $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @param array<array<string>> $body
     */
    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            Request::METHOD_POST,
            '/api/admin/settings',
            $body,
            token: $token,
            validateRequestSchema: false
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой тип' => [['type' => '']];

        yield 'пустое значение' => [['value' => '']];

        yield 'тип не из списка enum' => [['type' => 'incorrect-type']];
    }
}
