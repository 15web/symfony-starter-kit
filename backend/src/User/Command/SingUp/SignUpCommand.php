<?php

declare(strict_types=1);

namespace App\User\Command\SingUp;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

final class SignUpCommand implements ApiRequest
{
    public function __construct(public readonly string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }
}
