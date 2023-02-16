<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article\Admin;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Админка, обновление статьи
 */
final class UpdateArticleTest extends ApiWebTestCase
{
    /**
     * @testdox Статья обновлена
     */
    public function testSuccess(): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = $title = 'Статья 2';
        $body['alias'] = $alias = 'statya 2';
        $body['body'] = $content = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/admin/articles/{$articleId}/update", $body, token: $token);

        self::assertSuccessResponse($response);

        $articleResponse = self::jsonDecode($response->getContent());

        self::assertNotNull($articleResponse['id']);
        self::assertSame($articleResponse['title'], $title);
        self::assertSame($articleResponse['alias'], $alias);
        self::assertSame($articleResponse['body'], $content);
        self::assertNotNull($articleResponse['createdAt']);
        self::assertNotNull($articleResponse['updatedAt']);
    }

    /**
     * @testdox Статья не найдена
     */
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
        $response = self::request('POST', "/api/admin/articles/{$articleId}/update", $body, token: $token);
        self::assertNotFound($response);
    }

    /**
     * @testdox Нельзя обновить статью, такой алиас уже существует
     */
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

        $response = self::request('POST', "/api/admin/articles/{$articleId}/update", $body, token: $token);

        self::assertApiError($response, 2);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = [];
        $body['title'] = 'Статья 2';
        $body['alias'] = 'statya 2';
        $body['body'] = 'Контент 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/admin/articles/{$articleId}/update", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @dataProvider notValidRequestProvider
     *
     * @testdox Неправильный запрос
     */
    public function testBadRequest(array $body): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья', 'statya', '<p>Контент</p>', $token);

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            'POST',
            "/api/admin/articles/{$articleId}/update",
            $body,
            token: $token,
            disableValidateRequestSchema: true
        );

        self::assertBadRequest($response);
    }

    public function notValidRequestProvider(): \Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой заголовок' => [['title' => '']];

        yield 'пустой алиас' => [['alias' => '']];
    }
}
