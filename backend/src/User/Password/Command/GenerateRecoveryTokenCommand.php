<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Infrastructure\ValueObject\Email;

/**
 * Команда для запроса на восстановление пароля
 */
final readonly class GenerateRecoveryTokenCommand
{
    public function __construct(public Email $email) {}
}
