<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class Setting extends ApiWebTestCase
{
    public static function list(): array
    {
        $token = User::auth();

        $response = self::request(Request::METHOD_GET, '/api/admin/settings', token: $token);

        self::assertSuccessResponse($response);

        return self::jsonDecode($response->getContent());
    }

    public static function publicList(): array
    {
        $response = self::request(Request::METHOD_GET, '/api/settings');

        self::assertSuccessResponse($response);

        return self::jsonDecode($response->getContent());
    }
}
