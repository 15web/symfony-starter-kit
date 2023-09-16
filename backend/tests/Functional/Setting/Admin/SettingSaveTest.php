<?php

declare(strict_types=1);

namespace App\Tests\Functional\Setting\Admin;

use App\Setting\Domain\SettingType;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;
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
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/setting/save', $body, token: $token);

        self::assertSuccessResponse($response);

        $responseList = self::request(Request::METHOD_GET, '/api/admin/settings', token: $token);
        self::assertSuccessResponse($responseList);

        $settings = self::jsonDecode($responseList->getContent());

        foreach ($settings as $setting) {
            if ($setting['type'] !== SettingType::EMAIL_SITE) {
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

        $response = self::request(Request::METHOD_POST, '/api/admin/setting/save', $body, token: $token);
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

        $response = self::request(Request::METHOD_POST, '/api/admin/setting/save', $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            Request::METHOD_POST,
            '/api/admin/setting/save',
            $body,
            token: $token,
            disableValidateRequestSchema: true
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
