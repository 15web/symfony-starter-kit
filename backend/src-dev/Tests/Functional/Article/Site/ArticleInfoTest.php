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
#[TestDox('Получение информации по статье')]
final class ArticleInfoTest extends ApiWebTestCase
{
    #[TestDox('Статья создана, информация получена')]
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
            uri: \sprintf('/api/articles/%s', $alias),
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{
         *     id: string|null,
         *     title: string,
         *     alias: string,
         *     body: string,
         *     createdAt: string|null,
         *     updatedAt: string|null
         *    }
         * } $article */
        $article = self::jsonDecode($response->getContent());

        self::assertSame($article['data']['title'], $title);
        self::assertSame($article['data']['body'], $content);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth('admin@example.test');

        Article::create(
            title: 'Статья',
            alias: 'statya',
            content: '<p>Контент</p>',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/articles/another-alias',
        );

        self::assertNotFound($response);
    }
}
