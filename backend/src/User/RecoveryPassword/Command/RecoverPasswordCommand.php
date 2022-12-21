<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\User\RecoveryPassword\Domain\RecoveryToken;
use Webmozart\Assert\Assert;

final class RecoverPasswordCommand implements ApiRequest
{
    public function __construct(
        public readonly RecoveryToken $recoverToken,
        public readonly string $password,
    ) {
        Assert::notEmpty($password);
        Assert::minLength($password, 6);
    }
}
