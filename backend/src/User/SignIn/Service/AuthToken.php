<?php

declare(strict_types=1);

namespace App\User\SignIn\Service;

use Symfony\Component\Uid\Uuid;

/**
 * Токен аутентификации
 */
final readonly class AuthToken
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public Uuid $tokenId,
        public string $token
    ) {}
}
