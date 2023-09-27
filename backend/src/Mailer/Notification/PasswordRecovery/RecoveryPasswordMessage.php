<?php

declare(strict_types=1);

namespace App\Mailer\Notification\PasswordRecovery;

use App\Infrastructure\Message;
use App\Infrastructure\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

/**
 * Email для отправки токена восстановления пароля
 */
final readonly class RecoveryPasswordMessage implements Message
{
    public function __construct(public Uuid $token, public Email $email) {}
}
