<?php

declare(strict_types=1);

namespace App\User\SignUp\Query;

use Symfony\Component\Uid\Uuid;

/**
 * DTO пользователя
 */
final readonly class UserData
{
    public function __construct(public Uuid $id, public string $email) {}
}
