<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * ID пользователя
 */
final readonly class UserId
{
    public function __construct(public Uuid $value = new UuidV7()) {}

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
