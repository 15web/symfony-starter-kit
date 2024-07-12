<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\User\User\Domain\UserId;

/**
 * Запрос на смену текущего пароля
 */
final readonly class ChangePasswordCommand
{
    /**
     * @param non-empty-string $newPassword Новый пароль
     */
    public function __construct(
        public UserId $userId,
        public string $newPassword,
    ) {}
}
