<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

/**
 * Email уже занят, невозможно создать пользователя с такой почтой
 */
final class UserAlreadyExistException extends \Exception
{
}
