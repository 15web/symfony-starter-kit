<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 */
final class ArticleInfoTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::auth();

        Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request('GET', "/api/article/{$alias}/info");
        self::assertSuccessResponse($response);

        $article = self::jsonDecode($response->getContent());

        self::assertSame($article['title'], $title);
        self::assertSame($article['body'], $content);
    }

    public function testNotFound(): void
    {
        $token = User::auth();

        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $response = self::request('GET', '/api/article/another-alias/info');
        self::assertNotFound($response);
    }
}
