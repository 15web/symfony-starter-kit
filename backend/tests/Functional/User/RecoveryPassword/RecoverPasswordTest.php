<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\RecoveryPassword;

use App\Tests\Functional\SDK\ApiWebTestCase;
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
        $body = [];
        $body['email'] = $userEmail = 'first@example.com';
        $body['password'] = '123QWE';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);

        $body = [];
        $body['email'] = $userEmail;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/request-password-recovery', $body);
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var Message $sentEmail */
        $sentEmail = self::getMailerMessage();

        /** @var string $recoverToken */
        $recoverToken = $sentEmail->getHeaders()->get('recoverToken')?->getBody();

        self::assertNotEmpty($recoverToken);

        $body = json_encode([
            'password' => $password = '123456',
        ], JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/recover-password/{$recoverToken}", $body);
        self::assertSuccessResponse($response);

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-in', $body);
        self::assertSuccessResponse($response);
    }

    #[TestDox('Пользователь не найден')]
    public function testUserNotFound(): void
    {
        $body = [
            'password' => '123456',
        ];
        $token = Uuid::v7();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(Request::METHOD_POST, "/api/recover-password/{$token}", $body, disableValidateRequestSchema: true);

        self::assertNotFound($response);
    }

    #[DataProvider('notValidEmailDataProvider')]
    #[TestDox('Неверный запрос')]
    public function testBadRequest(?string $email): void
    {
        $body = ['email' => $email];
        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(Request::METHOD_POST, '/api/request-password-recovery', $body, disableValidateRequestSchema: true);

        self::assertBadRequest($response);
    }

    public static function notValidEmailDataProvider(): Iterator
    {
        yield 'null' => [null];

        yield 'Пустая строка' => [''];

        yield 'Неверный Email' => ['testEmail'];
    }
}
