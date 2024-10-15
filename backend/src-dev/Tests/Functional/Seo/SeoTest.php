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
            keywords: $keywords = 'keywords',
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/seo/%s/%s', $type, $id),
        );
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
        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/seo/%s/%s', SeoResourceType::ARTICLE->value, Uuid::v7()),
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{title: ?string, description: ?string, keywords: ?string}
         * } $seo
         */
        $seo = self::jsonDecode($response->getContent());

        self::assertNull($seo['data']['title']);
        self::assertNull($seo['data']['description']);
        self::assertNull($seo['data']['keywords']);
    }
}
