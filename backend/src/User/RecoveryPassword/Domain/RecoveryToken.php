<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Domain;

use App\Infrastructure\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

#[ORM\Embeddable]
final class RecoveryToken implements ValueObject
{
    public function __construct(
        #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
        public readonly ?Uuid $value = new UuidV4()
    ) {
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
