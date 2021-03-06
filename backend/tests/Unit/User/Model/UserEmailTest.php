<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Model;

use App\User\Model\UserEmail;
use PHPUnit\Framework\TestCase;

final class UserEmailTest extends TestCase
{
    public function testCorrectValue(): void
    {
        $email = new UserEmail($expectedEmail = 'test@example.com');

        self::assertSame($expectedEmail, $email->getValue());
    }

    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserEmail('');
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserEmail('test');
    }
}
