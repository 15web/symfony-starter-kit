<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\SignUp;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
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
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body);
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
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(Request::METHOD_POST, '/api/sign-up', $body);
        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body);

        self::assertApiError($response, ApiErrorCode::UserAlreadyExist->value);
    }

    #[TestDox('Неправильный запрос')]
    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test', 'password' => '123456'], JSON_THROW_ON_ERROR);
        $response = self::request(Request::METHOD_POST, '/api/sign-up', $body, validateRequestSchema: false);
        self::assertBadRequest($response);
    }
}
