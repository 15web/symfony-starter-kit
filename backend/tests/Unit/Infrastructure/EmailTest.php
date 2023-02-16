<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\Email;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Email
 */
final class EmailTest extends TestCase
{
    /**
     * @testdox Создание корректного email
     */
    public function testCorrectValue(): void
    {
        $userEmail = new Email($expectedEmail = 'test@example.com');

        self::assertSame($expectedEmail, $userEmail->value);
    }

    /**
     * @testdox Нельзя создать пустой email
     */
    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('');
    }

    /**
     * @testdox Невалидный формат email
     */
    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('test');
    }
}
