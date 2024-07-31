<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * ID токена пользователя
 */
final readonly class UserTokenId
{
    public function __construct(public Uuid $value = new UuidV7()) {}
}
