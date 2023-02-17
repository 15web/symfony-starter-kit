<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Получение информации по статье
 */
final class ArticleInfoTest extends ApiWebTestCase
{
    /**
     * @testdox Статья создана, информация получена
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request('GET', "/api/articles/{$alias}");
        self::assertSuccessResponse($response);

        $article = self::jsonDecode($response->getContent());

        self::assertSame($article['title'], $title);
        self::assertSame($article['body'], $content);
    }

    /**
     * @testdox Статья не найдена
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $response = self::request('GET', '/api/articles/another-alias');
        self::assertNotFound($response);
    }
}
