<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Infrastructure\Response;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Системная ошибка')]
final class SystemExceptionTest extends ApiWebTestCase
{
    #[TestDox('Урл не найден')]
    public function testUriNotFound(): void
    {
        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/not-found-api-uri',
        );

        self::assertNotFound($response);
    }
}
