<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\ValueObject;
use Symfony\Component\Uid\Uuid;

final class TaskCommentId implements ValueObject
{
    private readonly Uuid $value;

    public function __construct()
    {
        $this->value = Uuid::v4();
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }

    public function getValue(): Uuid
    {
        return $this->value;
    }
}
