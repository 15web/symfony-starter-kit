<?php

declare(strict_types=1);

namespace App\User\Query\User\Model;

use Symfony\Component\Uid\Uuid;

final class User
{
    public readonly UserId $id;
    public readonly string $email;

    public function __construct(Uuid $id, string $email)
    {
        $this->id = new UserId($id);
        $this->email = $email;
    }
}
