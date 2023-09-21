<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\Infrastructure\ValueObject\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Токен подтверждения регистрации
 */
#[ORM\Embeddable]
final readonly class ConfirmToken implements ValueObject
{
    public function __construct(#[ORM\Column(type: 'uuid', unique: true)]
    public Uuid $value)
    {
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
