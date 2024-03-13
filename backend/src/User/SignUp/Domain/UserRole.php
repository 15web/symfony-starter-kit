<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

/**
 * Роль пользователя
 */
enum UserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
}
