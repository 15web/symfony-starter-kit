<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use SensitiveParameter;

/**
 * Объект запроса на логин
 */
final readonly class SignInRequest
{
    /**
     * @param non-empty-string $email
     * @param non-empty-string $password
     */
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
