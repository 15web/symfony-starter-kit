<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seo\Admin;

use App\Seo\Domain\SeoResourceType;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Сохранение данных seo')]
final class SeoSaveTest extends ApiWebTestCase
{
    #[TestDox('seo данные сохранены')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $body = [];
        $body['type'] = SeoResourceType::ARTICLE->value;
        $body['identity'] = (string) Uuid::v7();
        $body['title'] = 'Заголовок seo';
        $body['description'] = 'description';
        $body['keywords'] = 'keywords';
        $bodyJson = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/seo/save', $bodyJson, token: $token);
        self::assertSuccessResponse($response);

        $url = '/api/seo/'.$body['type'].'/'.$body['identity'];

        $response = self::request(Request::METHOD_GET, $url);
        self::assertSuccessResponse($response);

        $seo = self::jsonDecode($response->getContent());

        self::assertSame($body['title'], $seo['title']);
        self::assertSame($body['description'], $seo['description']);
        self::assertSame($body['keywords'], $seo['keywords']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $body = [];
        $body['type'] = SeoResourceType::ARTICLE->value;
        $body['identity'] = (string) Uuid::v7();
        $body['title'] = 'Заголовок seo';
        $body['description'] = 'description';
        $body['keywords'] = 'keywords';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/seo/save', $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            Request::METHOD_POST,
            '/api/admin/seo/save',
            $body,
            token: $token,
            disableValidateRequestSchema: true
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];

        yield 'пустой заголовок' => [[
            'type' => SeoResourceType::ARTICLE->value,
            'identity' => (string) Uuid::v7(),
            'title' => '',
        ]];

        yield 'пустой description' => [[
            'type' => SeoResourceType::ARTICLE->value,
            'identity' => (string) Uuid::v7(),
            'title' => 'title',
            'description' => '',
        ]];

        yield 'пустой keywords' => [[
            'type' => SeoResourceType::ARTICLE->value,
            'identity' => (string) Uuid::v7(),
            'title' => 'title',
            'keywords' => '',
        ]];
    }
}
