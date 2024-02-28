<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\SDK;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;

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

        self::request(Request::METHOD_POST, '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(Request::METHOD_GET, "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-in', $body);

        /** @var array{
         *     data: array{token: string}
         * } $signUpResponse */
        $signUpResponse = self::jsonDecode($response->getContent());

        return $signUpResponse['data']['token'];
    }
}
