<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain;

use App\User\SignUp\Domain\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Тестирование ValueObject UserEmail
 */
final class UserEmailTest extends TestCase
{
    /**
     * @testdox UserEmail успешно создан
     */
    public function testCorrectValue(): void
    {
        $confirmToken = Uuid::v4();
        $userEmail = new UserEmail($expectedEmail = 'test@example.com', $confirmToken);

        self::assertSame($expectedEmail, $userEmail->value);
    }

    /**
     * @testdox Нельзя создать с пустым email
     */
    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('', $confirmToken);
    }

    /**
     * @testdox Невалидный email
     */
    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('test', $confirmToken);
    }
}
