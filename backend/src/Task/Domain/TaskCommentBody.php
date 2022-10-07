<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class TaskCommentBody implements ValueObject
{
    #[ORM\Column]
    private readonly string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        $this->value = $value;
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
