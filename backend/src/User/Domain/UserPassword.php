<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Infrastructure\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class UserPassword implements ValueObject
{
    #[ORM\Column]
    public readonly string $value;

    // указать тип для callable
    public function __construct(string $plaintextPassword, callable $hasher)
    {
        Assert::notEmpty($plaintextPassword);

        $hashedPassword = $hasher($plaintextPassword);

        Assert::notEq($plaintextPassword, $hashedPassword);

        $this->value = $hashedPassword;
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
