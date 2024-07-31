<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\User\User\Domain;

use App\User\User\Domain\UserPassword;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @internal
 */
#[TestDox('Тест пароля пользователя')]
final class UserPasswordTest extends TestCase
{
    #[TestDox('Создание хэша')]
    public function testHashMethod(): void
    {
        $userPassword = new UserPassword(
            cleanPassword: 'password',
            hashCost: 4,
        );

        $hash = $userPassword->hash();

        self::assertStringStartsWith('$2y$04', $hash);
    }

    #[TestDox('Проверка хэша')]
    public function testVerifyMethod(): void
    {
        $userPassword = new UserPassword(
            cleanPassword: 'password',
            hashCost: 4,
        );

        $hash = password_hash(
            password: 'password',
            algo: PASSWORD_BCRYPT,
            options: ['cost' => 4],
        );

        self::assertTrue($userPassword->verify($hash));
    }

    #[TestDox('Недостаточная длина пароля')]
    public function testInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserPassword(
            cleanPassword: 'tiny',
            hashCost: 4,
        );
    }
}
