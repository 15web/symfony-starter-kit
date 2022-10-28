<?php

declare(strict_types=1);

namespace App\User\SignUp\Notification;

use App\Infrastructure\Message;
use Webmozart\Assert\Assert;

final class NewPasswordMessage implements Message
{
    public function __construct(private readonly string $plaintextPassword, private readonly string $email)
    {
        Assert::notEmpty($plaintextPassword);
        Assert::notEmpty($email);
        Assert::email($email);
    }

    public function getPlaintextPassword(): string
    {
        return $this->plaintextPassword;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
