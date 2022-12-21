<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Notification;

use App\Infrastructure\Message;
use App\User\RecoveryPassword\Domain\RecoveryToken;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final class RecoverPasswordMessage implements Message
{
    public function __construct(public readonly RecoveryToken $recoverToken, public readonly string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }
}
