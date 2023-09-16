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
#[TestDox('Админка, получение информации о статье')]
final class ArticleInfoTest extends ApiWebTestCase
{
    #[TestDox('Получена информация по созданной статье')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $articleId = Article::createAndReturnId($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_GET, "/api/admin/articles/{$articleId}", token: $token);
        self::assertSuccessResponse($response);

        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['id']);
        self::assertSame($articleResponse['title'], $title);
        self::assertSame($articleResponse['alias'], $alias);
        self::assertSame($articleResponse['body'], $content);
        self::assertNotNull($articleResponse['createdAt']);
        self::assertNull($articleResponse['updatedAt']);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();
        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $articleId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_GET, "/api/admin/articles/{$articleId}", token: $token);
        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_GET, "/api/admin/articles/{$articleId}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
