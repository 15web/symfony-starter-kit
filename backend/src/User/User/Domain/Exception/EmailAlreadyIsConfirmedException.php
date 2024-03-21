<?php

declare(strict_types=1);

namespace App\User\User\Domain\Exception;

use Exception;

/**
 * Нельзя подтвердить уже подтвержденный email
 */
final class EmailAlreadyIsConfirmedException extends Exception {}
