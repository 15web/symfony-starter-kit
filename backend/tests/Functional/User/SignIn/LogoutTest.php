<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\SignIn;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Выхода из аккаунта
 */
final class LogoutTest extends ApiWebTestCase
{
    /**
     * @testdox Аутентификация, logout выполнен, по повторному logout ошибка доступа
     */
    public function testSuccessUseCase(): void
    {
        $token = User::auth();

        $response = self::request('GET', '/api/logout', token: $token);
        self::assertSuccessResponse($response);

        $response = self::request('GET', '/api/logout', token: $token);
        self::assertAccessDenied($response);
    }
}
