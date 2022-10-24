<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Infrastructure\ApiException\ApiErrorCode;
use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class ConfirmEmailTest extends ApiWebTestCase
{
    public function testCorrectConfirmEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];

        self::assertNotEmpty($confirmToken);

        $response = self::request('GET', "/api/confirm-email/{$confirmToken}");
        self::assertSuccessResponse($response);
    }

    public function testEmailAlreadyIsConfirmed(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];

        self::assertNotEmpty($confirmToken);
        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $response = self::request('GET', "/api/confirm-email/{$confirmToken}");

        self::assertApiError($response, ApiErrorCode::EmailAlreadyIsConfirmed->value);
    }

    public function testNotValidConfirmToken(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);
        self::assertEmailCount(1);

        $confirmToken = Uuid::v4();

        self::assertNotEmpty($confirmToken);

        $response = self::request('GET', "/api/confirm-email/{$confirmToken}");
        self::assertNotFound($response);
    }
}
