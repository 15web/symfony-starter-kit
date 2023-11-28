<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
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
        $token = User::auth();

        Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_GET, "/api/articles/{$alias}");
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
        $token = User::auth();

        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $response = self::request(Request::METHOD_GET, '/api/articles/another-alias');
        self::assertNotFound($response);
    }
}
