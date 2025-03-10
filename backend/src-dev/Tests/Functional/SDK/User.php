<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\SDK;

use App\User\User\Domain\UserRole;
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
    public static function auth(
        string $userEmail = 'first@example.com',
        ?UserRole $role = null,
    ): string {
        $password = '123456';
        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        if ($role !== null) {
            self::getConnection()
                ->createQueryBuilder()
                ->update('public.user')
                ->where('user_email_value = :email')
                ->set('user_role', ':role')
                ->setParameter('email', $userEmail)
                ->setParameter('role', $role->value)
                ->executeQuery();
        }

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

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
}
