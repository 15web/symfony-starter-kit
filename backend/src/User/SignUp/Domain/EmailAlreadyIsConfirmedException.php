<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

/**
 * Нельзя подтвердить уже подтвержденный email
 */
final class EmailAlreadyIsConfirmedException extends \Exception
{
}
