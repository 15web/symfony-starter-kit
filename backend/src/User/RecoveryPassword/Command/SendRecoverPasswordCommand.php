<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

final class SendRecoverPasswordCommand implements ApiRequest
{
    public function __construct(
        public readonly string $email,
    ) {
        Assert::notEmpty($email);
        Assert::email($email);
    }
}
