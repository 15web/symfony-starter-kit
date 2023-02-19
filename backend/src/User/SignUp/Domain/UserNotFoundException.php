<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use Exception;

/**
 * Пользователь не найден
 */
final class UserNotFoundException extends Exception
{
}
