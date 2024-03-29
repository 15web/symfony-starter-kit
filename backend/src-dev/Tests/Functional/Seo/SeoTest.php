<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Seo;

use App\Seo\Domain\SeoResourceType;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Seo;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Seo')]
final class SeoTest extends ApiWebTestCase
{
    #[TestDox('Получение seo данных')]
    public function testSuccess(): void
    {
        Seo::create(
            type: $type = SeoResourceType::ARTICLE->value,
            identity: $id = (string) Uuid::v7(),
            title: $title = 'title',
            description: $description = 'description',
            keywords: $keywords = 'keywords'
        );

        $response = self::request(Request::METHOD_GET, "/api/seo/{$type}/{$id}");
        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{title: string, description: string, keywords:string}
         * } $seo */
        $seo = self::jsonDecode($response->getContent());

        self::assertSame($seo['data']['title'], $title);
        self::assertSame($seo['data']['description'], $description);
        self::assertSame($seo['data']['keywords'], $keywords);
    }

    #[TestDox('Получение seo данных с несуществующим идентификатором')]
    public function testNotFound(): void
    {
        $response = self::request(Request::METHOD_GET, '/api/seo/'.SeoResourceType::ARTICLE->value.'/'.Uuid::v7());

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{title: string, description: string, keywords:string}
         * } $seo */
        $seo = self::jsonDecode($response->getContent());

        self::assertSame($seo['data']['title'], null);
        self::assertSame($seo['data']['description'], null);
        self::assertSame($seo['data']['keywords'], null);
    }
}
