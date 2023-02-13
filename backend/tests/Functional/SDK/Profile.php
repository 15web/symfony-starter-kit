<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class Profile extends ApiWebTestCase
{
    public static function save(string $name, string $phone, string $token): Response
    {
        $body = [];
        $body['name'] = $name;
        $body['phone'] = $phone;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request('POST', '/api/profile-save', $body, token: $token);
    }

    public static function info(string $token): Response
    {
        return self::request('GET', '/api/profile', token: $token);
    }
}
