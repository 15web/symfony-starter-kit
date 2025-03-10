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
     * Аутентификация пользователя
     *
     * @return string Access token
     */
    public static function auth(string $userEmail = 'user@example.test', string $password = '123456'): string
    {
        $body = [
            'email' => $userEmail,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        /** @var array{data: array{token: string}} $signInResponse */
        $signInResponse = self::jsonDecode($response->getContent());

        return $signInResponse['data']['token'];
    }

    /**
     * Регистрация пользователя по емейл, аутентификация.
     *
     * @return string Access token
     */
    public static function registerAndAuth(string $userEmail, string $password = '123456'): string
    {
        $body = [
            'email' => $userEmail,
            'password' => $password,
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        return self::auth(
            userEmail: $userEmail,
            password: $password,
        );
    }
}
