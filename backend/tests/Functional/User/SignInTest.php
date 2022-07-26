<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class SignInTest extends ApiWebTestCase
{
    public function testCorrectCredentials(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $emailMessage */
        $emailMessage = self::getMailerMessage();
        $context = $emailMessage->getContext();
        $password = $context['password'];

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

        self::assertNotEmpty($response['token']);
        self::assertSame($userEmail, $response['email']);
    }

    public function testInvalidPassword(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, true);

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
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $emailMessage */
        $emailMessage = self::getMailerMessage();
        $context = $emailMessage->getContext();
        $password = $context['password'];

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
        $response = self::request('POST', '/api/sign-in', $body, newClient: true, validateRequestSchema: false);

        self::assertBadRequest($response);
    }
}
