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
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Админка, обновление статьи')]
final class UpdateArticleTest extends ApiWebTestCase
{
    #[TestDox('Статья обновлена')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = $title = 'Статья 2';
        $body['alias'] = $alias = 'statya 2';
        $body['body'] = $content = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/admin/articles/{$articleId}", $body, token: $token);

        self::assertSuccessResponse($response);

        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['id']);
        self::assertSame($articleResponse['title'], $title);
        self::assertSame($articleResponse['alias'], $alias);
        self::assertSame($articleResponse['body'], $content);
        self::assertNotNull($articleResponse['createdAt']);
        self::assertNotNull($articleResponse['updatedAt']);
    }

    #[TestDox('Статья не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();
        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya 2';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $articleId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_POST, "/api/admin/articles/{$articleId}", $body, token: $token);
        self::assertNotFound($response);
    }

    #[TestDox('Нельзя обновить статью, такой алиас уже существует')]
    public function testExistArticleWithSuchAlias(): void
    {
        $token = User::auth();

        Article::create('Статья', 'statya1', '<p>Контент</p>', $token);
        $articleId = Article::createAndReturnId('Статья', 'statya2', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya1';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/admin/articles/{$articleId}", $body, token: $token);

        self::assertApiError($response, 2);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya 2';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/admin/articles/{$articleId}", $body, token: $notValidToken);

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
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            Request::METHOD_POST,
            "/api/admin/articles/{$articleId}",
            $body,
            token: $token,
            validateRequestSchema: false
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой заголовок' => [['title' => '']];

        yield 'пустой алиас' => [['alias' => '']];
    }
}
