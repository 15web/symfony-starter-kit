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
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Админка, обновление статьи')]
final class UpdateArticleTest extends ApiWebTestCase
{
    #[TestDox('Статья обновлена')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');
        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $body = [];
        $body['title'] = $title = 'Статья 2';
        $body['alias'] = $alias = 'statya 2';
        $body['body'] = $content = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            body: $body,
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{
         *     id: string|null,
         *     title: string,
         *     alias: string,
         *     body: string,
         *     createdAt: string|null,
         *     updatedAt: string|null
         *    }
         * } $articleResponse
         */
        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['data']['id']);
        self::assertSame($articleResponse['data']['title'], $title);
        self::assertSame($articleResponse['data']['alias'], $alias);
        self::assertSame($articleResponse['data']['body'], $content);
        self::assertNotNull($articleResponse['data']['createdAt']);
        self::assertNotNull($articleResponse['data']['updatedAt']);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth('admin@example.test');
        Article::create(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya 2';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $articleId = (string) Uuid::v7();
        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            body: $body,
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[TestDox('Нельзя обновить статью, такой алиас уже существует')]
    public function testExistArticleWithSuchAlias(): void
    {
        $token = User::auth('admin@example.test');

        Article::create(
            title: 'Статья',
            alias: 'statya1',
            content: '<p>Контент</p>',
            token: $token,
        );

        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya2',
            content: '<p>Контент</p>',
            token: $token,
        );

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya1';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            body: $body,
            token: $token,
        );

        self::assertApiError($response, 2);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth('admin@example.test');
        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya 2';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            body: $body,
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Пользователю доступ запрещен')]
    public function testForbidden(): void
    {
        $token = User::auth('admin@example.test');

        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $userToken = User::auth();

        $body = [
            'title' => 'Статья 2',
            'alias' => 'statya 2',
            'body' => 'Контент 2',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
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
        $token = User::auth('admin@example.test');
        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
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

        yield 'пустой заголовок' => [['title' => '']];

        yield 'пустой алиас' => [['alias' => '']];
    }
}
