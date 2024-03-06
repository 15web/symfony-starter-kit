<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\SignUp;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Подтверждение регистрации по email')]
final class ConfirmEmailTest extends ApiWebTestCase
{
    #[TestDox('Email подтвержден')]
    public function testCorrectConfirmEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);

        $response = self::request(Request::METHOD_GET, "/api/confirm-email/{$confirmToken}");
        self::assertSuccessResponse($response);
    }

    #[TestDox('Email уже подтвержден')]
    public function testEmailAlreadyIsConfirmed(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(Request::METHOD_POST, '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
        self::request(Request::METHOD_GET, "/api/confirm-email/{$confirmToken}");

        $response = self::request(Request::METHOD_GET, "/api/confirm-email/{$confirmToken}");

        self::assertApiError($response, ApiErrorCode::EmailAlreadyIsConfirmed->value);
    }

    #[TestDox('Регистрация не подтверждена, неверный токен')]
    public function testNotValidConfirmToken(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);
        self::assertEmailCount(1);

        $confirmToken = Uuid::v4();

        $response = self::request(Request::METHOD_GET, "/api/confirm-email/{$confirmToken}");
        self::assertNotFound($response);
    }
}
