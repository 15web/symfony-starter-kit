<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда для регистрации
 */
final class SignUpCommand implements ApiRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
        Assert::notEmpty($email);
        Assert::email($email);

        Assert::notEmpty($password);
        Assert::minLength($password, 6);
    }
}
