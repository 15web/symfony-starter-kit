<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article\Admin;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Функциональный тест админки списка статей
 */
final class ArticleListTest extends ApiWebTestCase
{
    /**
     * @testdox Получение списка статей
     */
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
            self::assertNotNull($article['alias']);
            self::assertNotNull($article['body']);
            self::assertNotNull($article['createdAt']);
            self::assertNull($article['updatedAt']);
        }
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен, невалидный токен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request('GET', '/api/admin/article/list', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
