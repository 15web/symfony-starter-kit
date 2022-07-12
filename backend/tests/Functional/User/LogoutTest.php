<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

final class LogoutTest extends ApiWebTestCase
{
    public function testSuccessUseCase(): void
    {
        $token = User::authFirst();

        $response = self::request('GET', '/api/logout', null, false, $token);
        self::assertSuccessResponse($response);

        $response = self::request('GET', '/api/logout', null, false, $token);
        self::assertAccessDenied($response);
    }
}
