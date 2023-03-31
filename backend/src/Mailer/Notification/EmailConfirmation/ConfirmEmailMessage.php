<?php

declare(strict_types=1);

namespace App\Mailer\Notification\EmailConfirmation;

use App\Infrastructure\Message;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Message для подтверждения почты
 */
final readonly class ConfirmEmailMessage implements Message
{
    public function __construct(private Uuid $confirmToken, private string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getConfirmToken(): Uuid
    {
        return $this->confirmToken;
    }
}
