<?php

declare(strict_types=1);

namespace App\Task\Model;

use App\Infrastructure\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class TaskName implements ValueObject
{
    #[ORM\Column]
    private readonly string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        $this->value = $value;
    }

    public function equalTo(ValueObject $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
