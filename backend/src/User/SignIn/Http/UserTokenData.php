<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use SensitiveParameter;

/**
 * Токен аутентификации
 */
final readonly class UserTokenData
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        #[SensitiveParameter]
        public string $token,
    ) {}
}
