<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;

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

        $response = self::request('GET', '/api/articles');
        self::assertSuccessResponse($response);

        $responseData = self::jsonDecode($response->getContent());
        $articles = $responseData['data'];
        $paginationResponse = $responseData['pagination'];

        self::assertCount(1, $articles);
        self::assertSame($articles[0]['title'], $title);
        self::assertSame($articles[0]['alias'], $alias);
        self::assertSame($articles[0]['body'], $content);

        self::assertSame($paginationResponse['total'], 1);
    }
}
