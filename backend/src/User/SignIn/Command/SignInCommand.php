<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

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
        public string $password,
        public Uuid $token,
    ) {}
}
