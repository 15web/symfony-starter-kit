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
#[TestDox('Админка, удаление статьи')]
final class RemoveArticleTest extends ApiWebTestCase
{
    #[TestDox('Статья удалена')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $articleId1 = Article::createAndReturnId(
            title: 'Статья1',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $articleId2 = Article::createAndReturnId(
            title: 'Статья2',
            alias: 'statya2',
            content: '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/admin/articles/%s', $articleId1),
            token: $token,
        );

        self::assertSuccessContentResponse($response);

        $articles = Article::list($token);

        self::assertCount(1, $articles);
        self::assertSame($articleId2, $articles[0]['id']);
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
            method: Request::METHOD_DELETE,
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
            title: 'Статья1',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/admin/articles/%s', $articleId),
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }
}
