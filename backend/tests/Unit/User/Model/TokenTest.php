<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Model;

use App\User\Domain\User;
use App\User\Domain\UserEmail;
use App\User\Domain\UserToken;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testCreation(): void
    {
        $user = new User(new UserEmail('test@example.com'));

        $token = new UserToken($user);

        self::assertNotNull($token->getCreatedAt());
        self::assertNotNull($token->getId());
        self::assertSame($user, $token->getUser());
    }
}
