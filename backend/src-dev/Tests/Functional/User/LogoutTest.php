<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Выхода из аккаунта')]
final class LogoutTest extends ApiWebTestCase
{
    #[TestDox('Аутентификация, logout выполнен, по повторному logout ошибка доступа')]
    public function testSuccessUseCase(): void
    {
        $token = User::auth();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/logout',
            token: $token,
        );

        self::assertSuccessResponse($response);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/logout',
            token: $token,
        );

        self::assertAccessDenied($response);
    }
}
