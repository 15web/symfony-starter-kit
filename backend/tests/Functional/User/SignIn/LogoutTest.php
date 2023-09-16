<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\SignIn;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;
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

        $response = self::request(Request::METHOD_GET, '/api/logout', token: $token);
        self::assertSuccessResponse($response);

        $response = self::request(Request::METHOD_GET, '/api/logout', token: $token);
        self::assertAccessDenied($response);
    }
}
