<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use App\Infrastructure\ValueObject;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * ID профиля
 */
final class ProfileId implements ValueObject
{
    public function __construct(public readonly Uuid $value = new UuidV4())
    {
    }

    public function equalTo(ValueObject $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
