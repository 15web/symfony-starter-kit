<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use Webmozart\Assert\Assert;

/**
 * Команда восстановления пароля
 */
final readonly class RecoverPasswordCommand
{
    /**
     * @param non-empty-string $password
     */
    public function __construct(public string $password)
    {
        Assert::minLength($password, 6);
    }
}
