<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\Infrastructure\ValueObject\ValueObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * Пароль пользователя
 */
#[ORM\Embeddable]
final readonly class UserPassword implements ValueObject
{
    #[ORM\Column]
    public string $value;

    /**
     * @param non-empty-string $hashedPassword
     */
    public function __construct(string $hashedPassword)
    {
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
