<?php

declare(strict_types=1);

namespace App\User\Domain;

enum UserRole: string
{
    case User = 'ROLE_USER';
}