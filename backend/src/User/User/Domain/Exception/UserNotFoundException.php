<?php

declare(strict_types=1);

namespace App\User\User\Domain\Exception;

use Exception;

/**
 * Пользователь не найден
 */
final class UserNotFoundException extends Exception {}
