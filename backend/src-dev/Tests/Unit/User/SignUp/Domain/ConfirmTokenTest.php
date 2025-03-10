<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\User\SignUp\Domain;

use App\User\User\Domain\ConfirmToken;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

/**
 * @internal
 */
#[TestDox('Токен подтверждения регистрации')]
final class ConfirmTokenTest extends TestCase
{
    #[TestDox('Токены идентичны')]
    public function testEquals(): void
    {
        $uuid = new UuidV7();
        $confirmToken1 = new ConfirmToken($uuid);
        $confirmToken2 = new ConfirmToken($uuid);

        self::assertTrue($confirmToken1->equalTo($confirmToken2));
    }

    #[TestDox('Разные токены')]
    public function testNotEquals(): void
    {
        $confirmToken1 = new ConfirmToken(new UuidV7());
        $confirmToken2 = new ConfirmToken(new UuidV7());

        self::assertFalse($confirmToken1->equalTo($confirmToken2));
    }
}
