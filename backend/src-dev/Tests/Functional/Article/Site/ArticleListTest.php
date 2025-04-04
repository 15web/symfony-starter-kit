<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article\Site;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Article;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Список статей')]
final class ArticleListTest extends ApiWebTestCase
{
    #[TestDox('Получен список из 1 созданной статьи')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        Article::create(
            title: $title = 'Статья',
            alias: $alias = 'statya',
            content: $content = '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/articles',
        );
        self::assertSuccessResponse($response);

        $responseData = self::jsonDecode($response->getContent());

        /** @var array<int, array{
         *     title: string,
         *     alias: string,
         *     body: string,
         * }> $articles
         */
        $articles = $responseData['data'];

        /** @var array{total: int} $paginationResponse */
        $paginationResponse = $responseData['pagination'];

        self::assertCount(1, $articles);
        self::assertSame($articles[0]['title'], $title);
        self::assertSame($articles[0]['alias'], $alias);
        self::assertSame($articles[0]['body'], $content);

        self::assertSame(1, $paginationResponse['total']);
    }
}
