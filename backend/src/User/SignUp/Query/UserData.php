<?php

declare(strict_types=1);

namespace App\User\SignUp\Query;

use App\Infrastructure\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

/**
 * DTO пользователя
 */
final readonly class UserData
{
    public function __construct(public Uuid $id, public Email $email) {}
}
