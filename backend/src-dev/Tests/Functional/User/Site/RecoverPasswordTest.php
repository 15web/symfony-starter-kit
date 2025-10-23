<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Message;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Восстановление пароля')]
final class RecoverPasswordTest extends ApiWebTestCase
{
    #[TestDox('Отправлен запрос на восстановление пароля, получено письмо с токеном, пароль восстановлен')]
    public function testSuccess(): void
    {
        $body = [
            'email' => $userEmail = 'first@example.com',
            'password' => '123QWE',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/request-password-recovery',
            body: json_encode(['email' => $userEmail], JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var Message $sentEmail */
        $sentEmail = self::getMailerMessage();

        /** @var string $recoverToken */
        $recoverToken = $sentEmail->getHeaders()->get('recoverToken')?->getBody();

        self::assertNotEmpty($recoverToken);

        $password = '123456';

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/recover-password/%s', $recoverToken),
            body: json_encode(['password' => $password], JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        $body = [
            'email' => $userEmail,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/recover-password/%s', $recoverToken),
            body: json_encode(['password' => $password], JSON_THROW_ON_ERROR),
        );

        self::assertNotFound($response);

        $userTokens = self::getConnection()
            ->createQueryBuilder()
            ->select('id')
            ->from('user_token')
            ->fetchAllAssociative();

        // После восстановления пароля все токены текущего пользователя удалены
        self::assertSame([], $userTokens);
    }

    #[TestDox('Пользователь не найден')]
    public function testUserNotFound(): void
    {
        $token = Uuid::v7();

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/recover-password/%s', $token),
            body: json_encode(['password' => '123456'], JSON_THROW_ON_ERROR),
            validateRequestSchema: false,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidEmailDataProvider')]
    #[TestDox('Неверный запрос')]
    public function testBadRequest(?string $email): void
    {
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/request-password-recovery',
            body: json_encode(['email' => $email], JSON_THROW_ON_ERROR),
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidEmailDataProvider(): Iterator
    {
        yield 'null' => [null];

        yield 'Пустая строка' => [''];

        yield 'Неверный Email' => ['testEmail'];
    }

    #[DataProvider('notValidPasswordDataProvider')]
    #[TestDox('Неверный запрос')]
    public function testConfirmBadRequest(?string $password): void
    {
        $body = [
            'email' => $userEmail = 'first@example.com',
            'password' => '123QWE',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/request-password-recovery',
            body: json_encode(['email' => $userEmail], JSON_THROW_ON_ERROR),
        );
        self::assertSuccessResponse($response);

        /** @var Message $sentEmail */
        $sentEmail = self::getMailerMessage();

        /** @var string $recoverToken */
        $recoverToken = $sentEmail->getHeaders()->get('recoverToken')?->getBody();

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/recover-password/%s', $recoverToken),
            body: json_encode(['password' => $password], JSON_THROW_ON_ERROR),
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidPasswordDataProvider(): Iterator
    {
        yield 'null' => [null];

        yield 'Пустой пароль' => [''];

        yield 'Короткий пароль' => ['1'];
    }
}
