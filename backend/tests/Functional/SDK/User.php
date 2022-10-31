<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

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
        $body = [];
        $body['email'] = $userEmail;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $password = $context['password'];

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);

        return self::jsonDecode($response->getContent())['token'];
    }
}
