<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain;

use App\User\SignUp\Domain\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 * @testdox Тестируемый класс: (App\Tests\Unit\User\Domain\UserEmail)
 */
final class UserEmailTest extends TestCase
{
    /**
     * @testdox Проверка метода testCorrectValue прошла успешно
     */
    public function testCorrectValue(): void
    {
        $confirmToken = Uuid::v4();
        $userEmail = new UserEmail($expectedEmail = 'test@example.com', $confirmToken);

        self::assertSame($expectedEmail, $userEmail->value);
    }

    /**
     * @testdox Проверка метода testEmptyEmail прошла успешно
     */
    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('', $confirmToken);
    }

    /**
     * @testdox Проверка метода testInvalidEmail прошла успешно
     */
    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confirmToken = Uuid::v4();

        new UserEmail('test', $confirmToken);
    }
}
