<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use Exception;

/**
 * Email пользователя еще не подтвержден
 */
final class EmailIsNotConfirmedException extends Exception {}
