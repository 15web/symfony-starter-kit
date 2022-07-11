<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class User extends ApiWebTestCase
{
    /**
     * @return string api token
     */
    public static function authFirst(): string
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $password = $context['password'];

        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);

        return (self::jsonDecode($response->getContent()))['token'];
    }
}
