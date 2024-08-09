<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\ValueObject\Email;
use App\User\User\Domain\AuthToken;
use SensitiveParameter;

/**
 * Команда для логина
 */
final readonly class SignInCommand
{
    /**
     * @param non-empty-string $password
     */
    public function __construct(
        public Email $email,
        #[SensitiveParameter]
        public string $password,
        public AuthToken $authToken,
    ) {}
}
