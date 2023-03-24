<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Notification;

use App\Infrastructure\Message;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Email для отправки токена восстановления пароля
 */
final readonly class RecoveryPasswordMessage implements Message
{
    public function __construct(public Uuid $token, public string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }
}
