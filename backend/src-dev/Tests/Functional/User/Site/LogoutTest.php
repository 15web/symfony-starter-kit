<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use App\User\User\Domain\UserRole;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/logout',
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Администратору доступ запрещен')]
    public function testAdminForbidden(): void
    {
        $adminToken = User::auth(role: UserRole::Admin);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/logout',
            token: $adminToken,
        );

        self::assertForbidden($response);
    }
}
