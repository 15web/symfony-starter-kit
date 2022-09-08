<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Infrastructure\ApiException\ApiErrorCode;
use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class SignUpTest extends ApiWebTestCase
{
    public function testCorrectSignUp(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $context = $email->getContext();
        $password = $context['password'];

        self::assertNotEmpty($password);
    }

    public function testCreationUserWithSameEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body);
        $response = self::request('POST', '/api/sign-up', $body);

        self::assertApiError($response, ApiErrorCode::UserAlreadyExist->value);
    }

    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test'], JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/sign-up', $body, newClient: true, validateRequestSchema: false);
        self::assertBadRequest($response);
    }
}
