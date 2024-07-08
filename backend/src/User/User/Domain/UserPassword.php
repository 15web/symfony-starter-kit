<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * Пароль пользователя
 */
#[ORM\Embeddable]
final readonly class UserPassword
{
    private const int MIN_LENGTH = 6;

    private const string HASH_ALGO = PASSWORD_BCRYPT;

    #[ORM\Column]
    public string $value;

    /**
     * @param non-empty-string $cleanPassword
     */
    public function __construct(
        private string $cleanPassword,
        private int $hashCost,
    ) {
        Assert::minLength($cleanPassword, self::MIN_LENGTH);

        $this->value = $this->hash();
    }

    /**
     * @return non-empty-string
     */
    public function hash(): string
    {
        /** @var non-empty-string $hash */
        $hash = password_hash(
            password: $this->cleanPassword,
            algo: self::HASH_ALGO,
            options: [
                'cost' => $this->hashCost,
            ],
        );

        return $hash;
    }

    public function verify(string $hash): bool
    {
        return password_verify(
            password: $this->cleanPassword,
            hash: $hash,
        );
    }
}
