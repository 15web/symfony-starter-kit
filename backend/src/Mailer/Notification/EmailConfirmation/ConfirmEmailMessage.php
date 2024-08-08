<?php

declare(strict_types=1);

namespace App\Mailer\Notification\EmailConfirmation;

use App\Infrastructure\Message;
use App\Infrastructure\ValueObject\Email;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

/**
 * Message для подтверждения почты
 */
final readonly class ConfirmEmailMessage implements Message
{
    public function __construct(
        #[SensitiveParameter]
        public Uuid $confirmToken,
        public Email $email,
    ) {}
}
