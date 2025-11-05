<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Setting\Admin;

use App\Setting\Domain\SettingType;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
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
        $token = User::auth('admin@example.test');

        $body = [];
        $body['type'] = SettingType::EMAIL_SITE;
        $body['value'] = 'symfony-starter-kit-test';
        $bodyJson = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: $bodyJson,
            token: $token,
        );

        self::assertSuccessResponse($response);

        $responseList = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/settings',
            token: $token,
        );
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

    #[TestDox('Такой тип настройки не поддерживается')]
    public function testBadType(): void
    {
        $token = User::auth('admin@example.test');

        $body = [];
        $body['type'] = 'not-found-type';
        $body['value'] = 'symfony-starter-kit-test';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: $body,
            token: $token,
        );

        self::assertBadRequest($response);
    }

    #[TestDox('Настройка не найдена')]
    public function testSettingNotFound(): void
    {
        /** @var Registry $registry */
        $registry = self::getContainer()->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();

        $connection->delete('setting', ['type' => SettingType::EMAIL_SITE->value]);

        $token = User::auth('admin@example.test');

        $body = [];
        $body['type'] = SettingType::EMAIL_SITE->value;
        $body['value'] = 'symfony-starter-kit-test';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: $body,
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $body = [];
        $body['type'] = SettingType::EMAIL_SITE;
        $body['value'] = 'symfony-starter-kit-test';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: $body,
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Пользователю доступ запрещен')]
    public function testForbidden(): void
    {
        $userToken = User::auth();

        $body = [
            'type' => SettingType::EMAIL_SITE,
            'value' => 'symfony-starter-kit-test',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $userToken,
        );

        self::assertForbidden($response);
    }

    /**
     * @param array<array<string>> $body
     */
    #[DataProvider('provideBadRequestCases')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth('admin@example.test');

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/settings',
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function provideBadRequestCases(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой тип' => [['type' => '']];

        yield 'пустое значение' => [['value' => '']];

        yield 'тип не из списка enum' => [['type' => 'incorrect-type']];
    }
}
