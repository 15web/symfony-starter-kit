<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article\Admin;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
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

        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['id']);
        self::assertSame($articleResponse['title'], $title);
        self::assertSame($articleResponse['alias'], $alias);
        self::assertSame($articleResponse['body'], $content);
        self::assertNotNull($articleResponse['createdAt']);
        self::assertNull($articleResponse['updatedAt']);
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

    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(Request::METHOD_POST, '/api/admin/articles/create', $body, token: $token, disableValidateRequestSchema: true);
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
