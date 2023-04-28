<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\SignUp\Domain;

use App\User\SignUp\Domain\ConfirmToken;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV4;

/**
 * @internal
 */
#[TestDox('Токен подтверждения регистрации')]
final class ConfirmTokenTest extends TestCase
{
    #[TestDox('Токены идентичны')]
    public function testEquals(): void
    {
        $uuid = new UuidV4();
        $confirmToken1 = new ConfirmToken($uuid);
        $confirmToken2 = new ConfirmToken($uuid);

        self::assertTrue($confirmToken1->equalTo($confirmToken2));
    }

    #[TestDox('Разные токены')]
    public function testNotEquals(): void
    {
        $confirmToken1 = new ConfirmToken(new UuidV4());
        $confirmToken2 = new ConfirmToken(new UuidV4());

        self::assertFalse($confirmToken1->equalTo($confirmToken2));
    }
}
