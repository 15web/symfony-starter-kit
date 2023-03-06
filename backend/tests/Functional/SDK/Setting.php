<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

/**
 * @internal
 */
final class Setting extends ApiWebTestCase
{
    public static function list(): array
    {
        $token = User::auth();

        $response = self::request('GET', '/api/admin/settings', token: $token);

        self::assertSuccessResponse($response);

        return self::jsonDecode($response->getContent());
    }

    public static function publicList(): array
    {
        $response = self::request('GET', '/api/settings');

        self::assertSuccessResponse($response);

        return self::jsonDecode($response->getContent());
    }
}
