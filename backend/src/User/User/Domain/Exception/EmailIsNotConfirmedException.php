<?php

declare(strict_types=1);

namespace App\User\User\Domain\Exception;

use Exception;

/**
 * Email пользователя еще не подтвержден
 */
final class EmailIsNotConfirmedException extends Exception {}
