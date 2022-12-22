<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Infrastructure\ApiException\ApiErrorCode;
use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * @internal
 */
final class SignInTest extends ApiWebTestCase
{
    public function testCorrectCredentials(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];
        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

        self::assertNotEmpty($response['token']);
    }

    public function testInvalidPassword(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];
        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertAccessDenied($response);
    }

    public function testInvalidEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];
        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = 'invalid@example.com';
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertAccessDenied($response);
    }

    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test', 'password' => ''], JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/sign-in', $body, newClient: true, disableValidateRequestSchema: true);

        self::assertBadRequest($response);
    }

    public function testNotConfirmedEmail(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertApiError($response, ApiErrorCode::EmailIsNotConfirmed->value);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $confirmToken = $context['confirmToken'];

        self::assertNotEmpty($confirmToken);
    }
}
