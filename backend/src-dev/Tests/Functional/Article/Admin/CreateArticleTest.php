<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Article\Admin;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Article;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Админка, создание статьи')]
final class CreateArticleTest extends ApiWebTestCase
{
    #[TestDox('Статья создана')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = Article::create($title = 'Статья', $alias = 'statya', $content = '<p>Контент</p>', $token);

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
         * } $articleResponse */
        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['data']['id']);
        self::assertSame($articleResponse['data']['title'], $title);
        self::assertSame($articleResponse['data']['alias'], $alias);
        self::assertSame($articleResponse['data']['body'], $content);
        self::assertNotNull($articleResponse['data']['createdAt']);
        self::assertNull($articleResponse['data']['updatedAt']);
    }

    #[TestDox('Нельзя создать статьи с одинаковым алиасом')]
    public function testExistArticleWithSuchAlias(): void
    {
        $token = User::auth();

        Article::create('Статья', $alias = 'statya', '<p>Контент</p>', $token);

        $response = Article::create('Статья2', $alias, '<p>Контент</p>', $token);

        self::assertApiError($response, 2);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = Article::create('Статья', 'statya', '<p>Контент</p>', $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @param array<int|string> $body
     */
    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(Request::METHOD_POST, '/api/admin/articles', $body, token: $token, validateRequestSchema: false);
        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное именование поля' => [['badKey']];

        yield 'пустой заголовок' => [['title' => '', 'alias' => 'alias']];

        yield 'пустой алиас' => [['title' => 'title', 'alias' => '']];
    }
}
