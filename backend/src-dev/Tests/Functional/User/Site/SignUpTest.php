<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Регистрация пользователя')]
final class SignUpTest extends ApiWebTestCase
{
    #[TestDox('Регистрация выполнена, токен получен')]
    public function testCorrectSignUp(): void
    {
        $body = [
            'email' => 'first@example.com',
            'password' => '123456',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
    }

    #[TestDox('Пользователь с таким email уже существует')]
    public function testCreationUserWithSameEmail(): void
    {
        $body = [
            'email' => 'first@example.com',
            'password' => '123456',
        ];

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        self::assertApiError($response, ApiErrorCode::UserAlreadyExist->value);
    }

    #[TestWith(['', 'password'], 'пустой емейл')]
    #[TestWith(['test', 'password'], 'невалидный емейл')]
    #[TestWith(['test@example.test', ''], 'пустой пароль')]
    #[TestWith(['test@example.test', '1'], 'короткий пароль')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(string $email, string $password): void
    {
        $body = [
            'email' => $email,
            'password' => $password,
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }
}
