<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\ValueObject\Email;

/**
 * Команда для регистрации
 */
final readonly class SignUpCommand
{
    /**
     * @param non-empty-string $password
     */
    public function __construct(
        public Email $email,
        public string $password
    ) {}
}
