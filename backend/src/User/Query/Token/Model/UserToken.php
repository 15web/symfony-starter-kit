<?php

declare(strict_types=1);

namespace App\User\Query\Token\Model;

use Symfony\Component\Uid\Uuid;

final class UserToken
{
    public readonly UserTokenId $id;

    public function __construct(
        Uuid $id
    ) {
        $this->id = new UserTokenId($id);
    }
}
