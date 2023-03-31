<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда восстановления пароля
 */
#[ApiRequest]
final readonly class RecoverPasswordCommand
{
    public function __construct(public string $password)
    {
        Assert::notEmpty($password);
        Assert::minLength($password, 6);
    }
}
