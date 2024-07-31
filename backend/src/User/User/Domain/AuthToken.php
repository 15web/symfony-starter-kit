<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Override;
use Stringable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * Токен аутентификации
 */
final readonly class AuthToken implements Stringable
{
    private const string HASH_ALGO = PASSWORD_BCRYPT;

    /**
     * @param UserTokenId $tokenId Идентификатор токена
     * @param Uuid $token Токен
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        public UserTokenId $tokenId,
        public Uuid $token,
        private int $hashCost,
    ) {}

    /**
     * @return non-empty-string
     */
    #[Override]
    public function __toString(): string
    {
        return sprintf('%s-%s', $this->tokenId->value, $this->token);
    }

    /**
     * @param int<min, 4> $hashCost
     */
    public static function generate(int $hashCost): self
    {
        return new self(
            tokenId: new UserTokenId(),
            token: new UuidV7(),
            hashCost: $hashCost,
        );
    }

    /**
     * @param int<min, 4> $hashCost
     */
    public static function createFromString(string $token, int $hashCost): self
    {
        $tokenId = substr($token, 0, 36);
        $tokenBody = substr($token, 37);

        return new self(
            tokenId: new UserTokenId(Uuid::fromString($tokenId)),
            token: Uuid::fromString($tokenBody),
            hashCost: $hashCost,
        );
    }

    /**
     * @return non-empty-string
     */
    public function hash(): string
    {
        /** @var non-empty-string $hash */
        $hash = password_hash(
            password: (string) $this->token,
            algo: self::HASH_ALGO,
            options: [
                'cost' => $this->hashCost,
            ],
        );

        return $hash;
    }

    public function verify(UserToken $userToken): bool
    {
        return password_verify(
            password: (string) $this->token,
            hash: $userToken->getHash(),
        );
    }
}
