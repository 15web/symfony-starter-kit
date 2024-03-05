<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Auth;

use Exception;

/**
 * Ошибка, связанная с токеном
 */
final class TokenException extends Exception {}
