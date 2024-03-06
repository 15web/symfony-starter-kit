<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article\Admin;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Article;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Админка, получение списка статей')]
final class ArticleListTest extends ApiWebTestCase
{
    #[TestDox('Получен список из созданных статей')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $articleId1 = Article::createAndReturnId($title1 = 'Статья1', 'statya', '<p>Контент</p>', $token);
        $articleId2 = Article::createAndReturnId($title2 = 'Статья2', 'statya2', '<p>Контент</p>', $token);

        $articles = Article::list($token);

        self::assertCount(2, $articles);

        foreach ($articles as $article) {
            self::assertContains($article['id'], [$articleId1, $articleId2]);
            self::assertContains($article['title'], [$title1, $title2]);
            self::assertNotEmpty($article['alias']);
            self::assertNotEmpty($article['body']);
            self::assertNotEmpty($article['createdAt']);
            self::assertNull($article['updatedAt']);
        }
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request(Request::METHOD_GET, '/api/admin/articles', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
