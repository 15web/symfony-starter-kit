<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 */
final class ArticlePaginationTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::auth();
        for ($i = 1; $i <= 15; ++$i) {
            Article::create('Статья'.$i, 'statya'.$i, '<p>Контент</p>', token: $token);
        }

        $response = self::request('GET', '/api/article/pagination-list');
        self::assertSuccessResponse($response);

        $article = self::jsonDecode($response->getContent());

        self::assertSame($article['items'][0]['title'], 'Статья1');
        self::assertSame($article['items'][0]['alias'], 'statya1');
        self::assertSame($article['itemsPerPage'], 10);
        self::assertSame($article['totalCount'], 15);
        self::assertSame($article['currentPage'], 1);
        self::assertSame($article['pageCount'], 2);
    }

    public function testIncorrectCount(): void
    {
        $response = self::request('GET', '/api/article/pagination-list?count=15');
        self::assertBadRequest($response);
    }
}
