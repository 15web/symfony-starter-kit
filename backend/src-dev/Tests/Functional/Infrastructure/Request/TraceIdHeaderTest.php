<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Infrastructure\Request;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Profile;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Заголовок X-Request-TraceId')]
final class TraceIdHeaderTest extends ApiWebTestCase
{
    #[TestDox('Заголовок добавлен в ответ')]
    public function testContainsHeader(): void
    {
        $token = User::auth();

        Profile::save(
            name: 'Name',
            phone: '79990001234',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/profile',
            token: $token,
        );

        self::assertSuccessResponse($response);

        self::assertNotNull(
            $response->headers->get('X-Request-TraceId'),
        );
    }

    #[TestDox('Заголовок не добавлен в ответ')]
    public function testIgnoredRoute(): void
    {
        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/ping',
        );

        self::assertSuccessResponse($response);

        self::assertNull(
            $response->headers->get('X-Request-TraceId'),
        );
    }
}
