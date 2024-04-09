<?php

declare(strict_types=1);

namespace App\User\SignIn\Service;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use Override;

/**
 * Сервис хеширования токена аутентификации
 */
#[AsService]
final class AuthTokenHasher implements Hasher
{
    private const string HASH_ALGO = 'sha256';

    #[Override]
    public function hash(string $data): string
    {
        return hash(self::HASH_ALGO, $data);
    }

    #[Override]
    public function verify(string $data, string $hash): bool
    {
        return $this->hash($data) === $hash;
    }
}
