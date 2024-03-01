<?php

declare(strict_types=1);

namespace App\Infrastructure\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Webmozart\Assert\Assert;

/**
 * Email
 */
#[ORM\Embeddable]
final readonly class Email implements ValueObject
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        #[ORM\Column]
        public string $value,
    ) {
        Assert::email($value);
    }

    /**
     * @param object $other
     */
    #[Override]
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
