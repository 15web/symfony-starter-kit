<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[TestDox('Аутентификация')]
final class SignInTest extends ApiWebTestCase
{
    #[TestDox('Регистрация выполнена, подтвержден email, аутентификация выполнена')]
    public function testCorrectCredentials(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: $body,
        );
        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{token: string}
         * } $signInResponse
         */
        $signInResponse = self::jsonDecode($response->getContent());

        self::assertNotEmpty($signInResponse['data']['token']);
    }

    #[TestDox('Неверный пароль')]
    public function testInvalidPassword(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: $body,
        );

        self::assertApiError($response, ApiErrorCode::Unauthenticated->value);
    }

    #[TestDox('Пользователя с таким email не существует')]
    public function testInvalidEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $body = [];
        $body['email'] = 'invalid@example.com';
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: $body,
        );

        self::assertApiError($response, ApiErrorCode::Unauthenticated->value);
    }

    #[TestDox('Неправильный запрос')]
    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test', 'password' => ''], JSON_THROW_ON_ERROR);
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: $body,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    #[TestDox('Логиниться можно только с подтвержденным Email, повторная отправка письма выполнена')]
    public function testNotConfirmedEmail(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: $body,
        );

        self::assertApiError($response, ApiErrorCode::EmailIsNotConfirmed->value);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
    }

    #[TestDox('Превышено количество запросов')]
    public function testTooManyRequests(): void
    {
        User::auth();

        $body = [
            'email' => 'first@example.com',
            'password' => 'password',
        ];

        $request = static fn (): Response => self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            resetRateLimiter: false,
        );

        for ($i = 0; $i < 3; ++$i) {
            self::assertApiError(($request)(), ApiErrorCode::Unauthenticated->value);
        }

        $response = ($request)();
        self::assertTooManyRequests(($request)());

        self::assertSame('0', $response->headers->get('X-RateLimit-Remaining'));
        self::assertSame('60', $response->headers->get('X-RateLimit-Retry-After'));
        self::assertSame('3', $response->headers->get('X-RateLimit-Limit'));
    }
}
