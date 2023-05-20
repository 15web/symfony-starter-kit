<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use Webmozart\Assert\Assert;

/**
 * Команда для запроса на восстановление пароля
 */
final readonly class GenerateRecoveryTokenCommand
{
    public function __construct(public string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }
}
