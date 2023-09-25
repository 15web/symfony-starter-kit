<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use Exception;

/**
 * Токен восстановления пароля не найден
 */
final class RecoveryTokenNotFoundException extends Exception {}
