<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seo;

use App\Seo\Domain\SeoResourceType;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Seo;
use PHPUnit\Framework\Attributes\TestDox;
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

        $response = self::request('GET', "/api/seo/{$type}/{$id}");
        self::assertSuccessResponse($response);

        $seo = self::jsonDecode($response->getContent());

        self::assertSame($seo['title'], $title);
        self::assertSame($seo['description'], $description);
        self::assertSame($seo['keywords'], $keywords);
    }

    #[TestDox('Получение seo данных с несуществующим идентификатором')]
    public function testNotFound(): void
    {
        $response = self::request('GET', '/api/seo/'.SeoResourceType::ARTICLE->value.'/'.Uuid::v7());

        self::assertSuccessResponse($response);
        $seo = self::jsonDecode($response->getContent());

        self::assertSame($seo['title'], null);
        self::assertSame($seo['description'], null);
        self::assertSame($seo['keywords'], null);
    }
}
