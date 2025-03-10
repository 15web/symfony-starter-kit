<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
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
        $userEmail = 'new@example.test';
        $password = 'password';

        $signUpBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($signUpBody, JSON_THROW_ON_ERROR),
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $signInBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($signInBody, JSON_THROW_ON_ERROR),
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
        $userEmail = 'new@example.test';
        $password = 'password';

        $signUpBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($signUpBody, JSON_THROW_ON_ERROR),
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $signInBody = [
            'email' => $userEmail,
            'password' => 'fakePassword',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($signInBody, JSON_THROW_ON_ERROR),
        );

        self::assertApiError($response, ApiErrorCode::Unauthenticated->value);
    }

    #[TestDox('Пользователя с таким email не существует')]
    public function testInvalidEmail(): void
    {
        $userEmail = 'new@example.test';
        $password = 'password';

        $signUpBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($signUpBody, JSON_THROW_ON_ERROR),
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $signInBody = [
            'email' => 'fake@example.test',
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($signInBody, JSON_THROW_ON_ERROR),
        );

        self::assertApiError($response, ApiErrorCode::Unauthenticated->value);
    }

    #[TestWith(['', 'password'], 'пустой емейл')]
    #[TestWith(['test', 'password'], 'невалидный емейл')]
    #[TestWith(['test@example.test', ''], 'пустой пароль')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(string $email, string $password): void
    {
        $body = [
            'email' => $email,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    #[TestDox('Логиниться можно только с подтвержденным Email, повторная отправка письма выполнена')]
    public function testNotConfirmedEmail(): void
    {
        $userEmail = 'new@example.test';
        $password = 'password';

        $signUpBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($signUpBody, JSON_THROW_ON_ERROR),
        );

        $signInBody = [
            'email' => $userEmail,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($signInBody, JSON_THROW_ON_ERROR),
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
            'email' => 'new@example.test',
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
