<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Tests\Functional\SDK\ApiWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class RecoverPasswordTest extends ApiWebTestCase
{
    public function testRecoverPassword(): void
    {
        $body = [];
        $body['email'] = $userEmail = 'first@example.com';
        $body['password'] = '123QWE';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-up', $body, newClient: true);
        self::assertSuccessResponse($response);

        $body = [];
        $body['email'] = $userEmail;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/recover-password-send', $body);
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $userEmail */
        $sentEmail = self::getMailerMessage();
        $recoverToken = $sentEmail->getHeaders()->get('recoverToken')?->getBody();

        self::assertNotEmpty($recoverToken);

        $body = json_encode([
            'recoverToken' => $recoverToken,
            'password' => $password = '123456',
        ], JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/recover-password', $body);
        self::assertSuccessResponse($response);

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertSuccessResponse($response);
    }

    /**
     * @dataProvider notValidEmailDataProvider
     */
    public function testSendBadRequest(?string $email): void
    {
        $body = ['email' => $email];
        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/recover-password-send', $body, disableValidateRequestSchema: true);

        self::assertBadRequest($response);
    }

    /**
     * @dataProvider notValidEmailDataProvider
     */
    public function testRecoverBadRequest(): void
    {
        $body = [
            'recoverToken' => Uuid::v4(),
            'password' => '12345',
        ];
        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/recover-password-send', $body, disableValidateRequestSchema: true);

        self::assertBadRequest($response);
    }

    public function testRecoverUserNotFound(): void
    {
        $body = [
            'recoverToken' => Uuid::v4(),
            'password' => '123456',
        ];
        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/recover-password', $body, disableValidateRequestSchema: true);

        self::assertNotFound($response);
    }

    /**
     * @return iterable<array<string|null>>
     */
    private function notValidEmailDataProvider(): iterable
    {
        yield [null];

        yield [''];

        yield ['testEmail'];
    }
}
