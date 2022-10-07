<?php

declare(strict_types=1);

namespace App\User\Query\User\Model;

use App\Infrastructure\ValueObject;
use Symfony\Component\Uid\Uuid;

final class UserId implements ValueObject
{
    public function __construct(public readonly Uuid $value)
    {
    }

    public function equalTo(ValueObject $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
