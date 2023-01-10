<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Infrastructure\ApiException\ApiErrorCode;
use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * @internal
 */
final class SignUpTest extends ApiWebTestCase
{
    public function testCorrectSignUp(): void
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
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
    }

    public function testCreationUserWithSameEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body);
        $response = self::request('POST', '/api/sign-up', $body);

        self::assertApiError($response, ApiErrorCode::UserAlreadyExist->value);
    }

    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test', 'password' => '123456'], JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/sign-up', $body, newClient: true, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }
}
