<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

/**
 * @internal
 */
final class User extends ApiWebTestCase
{
    /**
     * Регистрация пользователя по емейл, аутентификация.
     *
     * @return string api token
     */
    public static function auth(string $userEmail = 'first@example.com'): string
    {
        $password = '123456';
        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);

        return self::jsonDecode($response->getContent())['token'];
    }
}
