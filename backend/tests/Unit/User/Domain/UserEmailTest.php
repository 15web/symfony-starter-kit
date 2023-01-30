<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain;

use App\User\SignUp\Domain\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Email пользователя
 */
final class UserEmailTest extends TestCase
{
    /**
     * @testdox Создание корректного email
     */
    public function testCorrectValue(): void
    {
        $confirmToken = Uuid::v4();
        $userEmail = new UserEmail($expectedEmail = 'test@example.com', $confirmToken);

        self::assertSame($expectedEmail, $userEmail->value);
    }

    /**
     * @testdox Нельзя создать пустой email
     */
    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('', $confirmToken);
    }

    /**
     * @testdox Невалидный формат email
     */
    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('test', $confirmToken);
    }
}
