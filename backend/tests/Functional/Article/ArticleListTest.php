<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Функциональный тест списка статей
 */
final class ArticleListTest extends ApiWebTestCase
{
    /**
     * @testdox Получение списка статей
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request('GET', '/api/article/list');
        self::assertSuccessResponse($response);

        $articles = self::jsonDecode($response->getContent());

        self::assertCount(1, $articles);

        self::assertSame($articles[0]['title'], $title);
        self::assertSame($articles[0]['alias'], $alias);
        self::assertSame($articles[0]['body'], $content);
    }
}
