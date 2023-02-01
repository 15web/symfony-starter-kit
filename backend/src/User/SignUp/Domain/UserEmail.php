<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\Infrastructure\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Email пользователя
 */
#[ORM\Embeddable]
final class UserEmail implements ValueObject
{
    #[ORM\Column]
    public readonly string $value;

    #[ORM\Column(type: 'uuid', unique: true)]
    public readonly Uuid $confirmToken;

    #[ORM\Column]
    private bool $isConfirmed;

    public function __construct(string $value, Uuid $confirmToken)
    {
        Assert::notEmpty($value);
        Assert::email($value);

        $this->value = $value;
        $this->confirmToken = $confirmToken;
        $this->isConfirmed = false;
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }

    public function confirm(): void
    {
        if ($this->isConfirmed === true) {
            throw new EmailAlreadyIsConfirmedException('Email уже подтвержден');
        }

        $this->isConfirmed = true;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }
}
