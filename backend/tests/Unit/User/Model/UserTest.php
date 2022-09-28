<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Model;

use App\User\Domain\User;
use App\User\Domain\UserEmail;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCorrectCreation(): void
    {
        $user = new User(new UserEmail($expectedEmail = 'test@example.com'));

        self::assertSame($expectedEmail, $user->getUserEmail()->getValue());
        self::assertSame($expectedEmail, $user->getUserIdentifier());
        self::assertNull($user->getPassword());
        self::assertNotNull($user->getCreatedAt());

        self::assertCount(1, $user->getRoles());
        self::assertSame('ROLE_USER', $user->getRoles()[0]);
    }

    public function testCorrectPasswordStore(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $user->applyPassword($expected = 'password');

        self::assertSame($expected, $user->getPassword());
    }
}
