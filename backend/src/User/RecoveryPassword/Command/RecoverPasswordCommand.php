<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

/**
 * Команда восстановления пароля
 */
final readonly class RecoverPasswordCommand
{
    /**
     * @param non-empty-string $password
     */
    public function __construct(public string $password) {}
}
