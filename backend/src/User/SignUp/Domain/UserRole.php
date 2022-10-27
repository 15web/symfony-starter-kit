<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

enum UserRole: string
{
    case User = 'ROLE_USER';
}
