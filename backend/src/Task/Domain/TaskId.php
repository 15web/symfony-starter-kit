<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\ValueObject\ValueObject;
use Override;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * ID задачи
 */
final readonly class TaskId implements ValueObject
{
    public function __construct(public Uuid $value = new UuidV7()) {}

    #[Override]
    public function equalTo(ValueObject $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
