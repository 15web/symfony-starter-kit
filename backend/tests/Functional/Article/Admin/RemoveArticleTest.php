<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article\Admin;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
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

        $articleId1 = Article::createAndReturnId('Статья1', 'statya', '<p>Контент</p>', $token);
        $articleId2 = Article::createAndReturnId('Статья2', 'statya2', '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_DELETE, "/api/admin/articles/{$articleId1}", token: $token);
        self::assertSuccessContentResponse($response);

        $articles = Article::list($token);

        self::assertCount(1, $articles);
        self::assertSame($articleId2, $articles[0]['id']);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();

        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $articleId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_DELETE, "/api/admin/articles/{$articleId}", token: $token);
        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья1', 'statya', '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_DELETE, "/api/admin/articles/{$articleId}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
