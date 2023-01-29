<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Функциональный тест выхода из аккаунта
 */
final class LogoutTest extends ApiWebTestCase
{
    /**
     * @testdox Успешный выход из аккаунта
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
