<?php

declare(strict_types=1);

namespace App\User\User\Query;

use App\User\User\Domain\UserRole;
use Symfony\Component\Uid\Uuid;

/**
 * Данные пользователя
 */
final readonly class UserData
{
    /**
     * @param non-empty-string $email
     * @param non-empty-string $password
     */
    public function __construct(
        public Uuid $userId,
        public string $email,
        public UserRole $role,
        public string $password,
        public bool $isConfirmed,
        public Uuid $confirmToken,
    ) {}
}
