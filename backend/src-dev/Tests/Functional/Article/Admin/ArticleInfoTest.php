<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article\Admin;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Article;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Админка, получение информации о статье')]
final class ArticleInfoTest extends ApiWebTestCase
{
    #[TestDox('Получена информация по созданной статье')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $articleId = Article::createAndReturnId(
            title: $title = 'Статья',
            alias: $alias = 'statya',
            content: $content = '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
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
         * } $articleResponse */
        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['data']['id']);
        self::assertSame($articleResponse['data']['title'], $title);
        self::assertSame($articleResponse['data']['alias'], $alias);
        self::assertSame($articleResponse['data']['body'], $content);
        self::assertNotNull($articleResponse['data']['createdAt']);
        self::assertNull($articleResponse['data']['updatedAt']);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();
        Article::create(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $articleId = (string) Uuid::v4();
        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }
}
