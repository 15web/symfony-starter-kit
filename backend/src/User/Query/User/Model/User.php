<?php

declare(strict_types=1);

namespace App\User\Query\User\Model;

use Symfony\Component\Uid\Uuid;

final class User
{
    public readonly UserId $id;

    public function __construct(Uuid $id, public readonly string $email)
    {
        $this->id = new UserId($id);
    }
}
