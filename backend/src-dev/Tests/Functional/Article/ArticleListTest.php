<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article;

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
        $token = User::auth();

        Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_GET, '/api/articles');
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

        self::assertSame($paginationResponse['total'], 1);
    }
}
