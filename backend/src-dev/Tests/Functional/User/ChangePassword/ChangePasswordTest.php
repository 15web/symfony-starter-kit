<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\ChangePassword;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[TestDox('Смена пароля')]
final class ChangePasswordTest extends ApiWebTestCase
{
    #[TestDox('Успешная смена пароля')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $body = [
            'currentPassword' => '123456',
            'newPassword' => 'newPassword',
            'newPasswordConfirmation' => 'newPassword',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/change-password',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{token: string} $data */
        $data = self::jsonDecode($response->getContent())['data'];

        self::assertNotEmpty($data['token']);

        $userTokens = self::getConnection()
            ->createQueryBuilder()
            ->select(...['*'])
            ->from('user_token')
            ->fetchAllAssociative();

        // После смены пароля все старые токены текущего пользователя удалены
        self::assertCount(1, $userTokens);

        $body = [
            'email' => 'first@example.com',
            'password' => 'newPassword',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-in',
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );

        self::assertSuccessResponse($response);
    }

    #[TestDox('Текущий пароль указан неверно')]
    public function testIncorrectCurrentPassword(): void
    {
        $token = User::auth();

        $body = [
            'currentPassword' => 'fakePassword',
            'newPassword' => 'newPassword',
            'newPasswordConfirmation' => 'newPassword',
        ];

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/change-password',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );

        self::assertApiError($response, ApiErrorCode::Unauthenticated->value);
    }

    #[TestDox('Превышено количество запросов')]
    public function testTooManyRequests(): void
    {
        $token = User::auth();

        $body = [
            'currentPassword' => 'fakePassword',
            'newPassword' => 'newPassword',
            'newPasswordConfirmation' => 'newPassword',
        ];

        $request = static fn (): Response => self::request(
            method: Request::METHOD_POST,
            uri: '/api/change-password',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
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

    /**
     * @param array<array-key, mixed>|null $body
     */
    #[DataProvider('notValidPasswordDataProvider')]
    #[TestDox('Неверный запрос')]
    public function testBadRequest(?array $body): void
    {
        $token = User::auth();

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/change-password',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidPasswordDataProvider(): Iterator
    {
        yield 'null' => [[null]];

        yield 'Пустая строка' => [['']];

        yield 'Не указан текущий пароль' => [['newPassword' => 'newPassword', 'newPasswordConfirmation' => 'newPassword']];

        yield 'Не указан новый пароль' => [['currentPassword' => 'newPassword', 'newPasswordConfirmation' => 'newPassword']];

        yield 'Не указано подтверждение нового пароля' => [['currentPassword' => 'currentPassword', 'newPassword' => 'newPassword']];

        yield 'Новый пароль и его подтверждение не совпадают' => [['currentPassword' => 'currentPassword', 'newPassword' => 'newPassword1', 'newPasswordConfirmation' => 'newPassword2']];

        yield 'Новый пароль не отличается от текущего' => [['currentPassword' => 'currentPassword', 'newPassword' => 'currentPassword', 'newPasswordConfirmation' => 'currentPassword']];
    }
}
