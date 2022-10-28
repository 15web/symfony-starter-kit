<?php

declare(strict_types=1);

namespace App\User\SignUp\Query;

use Symfony\Component\Uid\Uuid;

final class UserData
{
    public function __construct(public readonly Uuid $id, public readonly string $email)
    {
    }
}
