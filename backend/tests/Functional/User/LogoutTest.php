<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Tests\DataFixtures\UserFixtures;
use App\Tests\Functional\SDK\ApiWebTestCase;

final class LogoutTest extends ApiWebTestCase
{
    public function testSuccessUseCase(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        $response = self::request('GET', '/api/logout', token: $token);
        self::assertSuccessResponse($response);

        $response = self::request('GET', '/api/logout', token: $token);
        self::assertAccessDenied($response);
    }
}
