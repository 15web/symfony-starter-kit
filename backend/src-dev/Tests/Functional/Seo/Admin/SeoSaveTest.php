<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Seo\Admin;

use App\Seo\Domain\SeoResourceType;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Сохранение данных seo')]
final class SeoSaveTest extends ApiWebTestCase
{
    #[TestDox('seo данные сохранены')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        $body = [];
        $body['type'] = SeoResourceType::ARTICLE->value;
        $body['identity'] = (string) Uuid::v7();
        $body['title'] = 'Заголовок seo';
        $body['description'] = 'description';
        $body['keywords'] = 'keywords';
        $bodyJson = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/seo',
            body: $bodyJson,
            token: $token,
        );

        self::assertSuccessResponse($response);

        $url = \sprintf('/api/seo/%s/%s', $body['type'], $body['identity']);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: $url,
        );
        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{title: string, description: string, keywords:string}
         * } $seo
         */
        $seo = self::jsonDecode($response->getContent());

        self::assertSame($body['title'], $seo['data']['title']);
        self::assertSame($body['description'], $seo['data']['description']);
        self::assertSame($body['keywords'], $seo['data']['keywords']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $body = [];
        $body['type'] = SeoResourceType::ARTICLE->value;
        $body['identity'] = (string) Uuid::v7();
        $body['title'] = 'Заголовок seo';
        $body['description'] = 'description';
        $body['keywords'] = 'keywords';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/seo',
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
            'type' => SeoResourceType::ARTICLE->value,
            'identity' => (string) Uuid::v7(),
            'title' => 'Заголовок seo',
            'description' => 'description',
            'keywords' => 'keywords',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/seo',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $userToken,
        );

        self::assertForbidden($response);
    }

    /**
     * @param array<int|string> $body
     */
    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/seo',
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой заголовок' => [
            [
                'type' => SeoResourceType::ARTICLE->value,
                'identity' => (string) Uuid::v7(),
                'title' => '',
            ],
        ];

        yield 'пустой description' => [
            [
                'type' => SeoResourceType::ARTICLE->value,
                'identity' => (string) Uuid::v7(),
                'title' => 'title',
                'description' => '',
            ],
        ];

        yield 'пустой keywords' => [
            [
                'type' => SeoResourceType::ARTICLE->value,
                'identity' => (string) Uuid::v7(),
                'title' => 'title',
                'keywords' => '',
            ],
        ];
    }
}
