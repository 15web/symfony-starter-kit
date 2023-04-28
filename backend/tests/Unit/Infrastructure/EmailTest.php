<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Email')]
final class EmailTest extends TestCase
{
    #[TestDox('Создание корректного email')]
    public function testCorrectValue(): void
    {
        $userEmail = new Email($expectedEmail = 'test@example.com');

        self::assertSame($expectedEmail, $userEmail->value);
    }

    #[TestDox('Нельзя создать пустой email')]
    public function testEmptyEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('');
    }

    #[TestDox('Невалидный формат email')]
    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('test');
    }
}
