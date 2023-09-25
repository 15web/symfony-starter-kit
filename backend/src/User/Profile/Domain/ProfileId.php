<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use App\Infrastructure\ValueObject\ValueObject;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * ID профиля
 */
final readonly class ProfileId implements ValueObject
{
    public function __construct(public Uuid $value = new UuidV7()) {}

    public function equalTo(ValueObject $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
