<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article\Admin;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Article;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Админка, получение статей по списку Id')]
final class ArticleListByIdsTest extends ApiWebTestCase
{
    #[TestDox('Получен список из созданных статей')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        $articleId1 = Article::createAndReturnId(
            title: $title1 = 'Статья1',
            alias: $alias1 = 'statya',
            content: $content1 = '<p>Контент</p>',
            token: $token,
        );

        $articleId2 = Article::createAndReturnId(
            title: $title2 = 'Статья2',
            alias: $alias2 = 'statya2',
            content: $content2 = '<p>Контент</p>',
            token: $token,
        );

        $body = [
            'ids' => [$articleId2, $articleId1],
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/articles-list',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array<int, array{
         *          id: string,
         *          title: string,
         *          alias: string,
         *          body: string,
         *          createdAt: string|null,
         *          updatedAt: string|null
         *     }>,
         *     pagination: array{total: int},
         * } $articles
         */
        $articles = self::jsonDecode($response->getContent());

        self::assertCount(2, $articles['data']);

        self::assertSame($articleId2, $articles['data'][0]['id']);
        self::assertSame($title2, $articles['data'][0]['title']);
        self::assertSame($alias2, $articles['data'][0]['alias']);
        self::assertSame($content2, $articles['data'][0]['body']);
        self::assertNotEmpty($articles['data'][0]['createdAt']);
        self::assertNull($articles['data'][0]['updatedAt']);

        self::assertSame($articleId1, $articles['data'][1]['id']);
        self::assertSame($title1, $articles['data'][1]['title']);
        self::assertSame($alias1, $articles['data'][1]['alias']);
        self::assertSame($content1, $articles['data'][1]['body']);
        self::assertNotEmpty($articles['data'][1]['createdAt']);
        self::assertNull($articles['data'][1]['updatedAt']);

        self::assertSame(2, $articles['pagination']['total']);
    }

    #[TestDox('Часть статей не найдено')]
    public function testArticleNotFound(): void
    {
        $token = User::auth('admin@example.test');

        $articleId = Article::createAndReturnId(
            title: $title = 'Статья1',
            alias: $alias = 'statya',
            content: $content = '<p>Контент</p>',
            token: $token,
        );

        $missingArticleId = '01954db6-9d00-75a8-8307-e4fc27fd28b8';

        $body = [
            'ids' => [$missingArticleId, $articleId],
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/articles-list',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array<int, array{
         *          id: string,
         *          title: string,
         *          alias: string,
         *          body: string,
         *          createdAt: string|null,
         *          updatedAt: string|null
         *     }>,
         *     pagination: array{total: int},
         * } $articles
         */
        $articles = self::jsonDecode($response->getContent());

        self::assertCount(1, $articles['data']);

        self::assertSame($articleId, $articles['data'][0]['id']);
        self::assertSame($title, $articles['data'][0]['title']);
        self::assertSame($alias, $articles['data'][0]['alias']);
        self::assertSame($content, $articles['data'][0]['body']);
        self::assertNotEmpty($articles['data'][0]['createdAt']);
        self::assertNull($articles['data'][0]['updatedAt']);

        self::assertSame(1, $articles['pagination']['total']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $body = [
            'ids' => ['01954db6-9d00-75a8-8307-e4fc27fd28b8'],
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/articles-list',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Пользователю доступ запрещен')]
    public function testForbidden(): void
    {
        $userToken = User::auth();

        $body = [
            'ids' => ['01954db6-9d00-75a8-8307-e4fc27fd28b8'],
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/articles-list',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $userToken,
        );

        self::assertForbidden($response);
    }

    /**
     * @param array{ids: list<string>} $body
     */
    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth('admin@example.test');

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/articles-list',
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'невалидное значение' => [['ids' => 'list']];

        yield 'пустой список' => [['ids' => ['']]];

        yield 'невалидный список' => [['ids' => ['fake']]];
    }
}
