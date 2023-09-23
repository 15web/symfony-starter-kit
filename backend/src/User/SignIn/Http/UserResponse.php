<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use Symfony\Component\Uid\Uuid;

/**
 * Токен аутентификации
 */
final readonly class UserResponse
{
    public function __construct(
        public Uuid $token,
    ) {
    }
}
