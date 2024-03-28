<?php

declare(strict_types=1);

namespace App\User\Service;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Сервис хеширования паролей
 */
#[AsService]
final readonly class PasswordHasher implements Hasher
{
    public function __construct(
        #[Autowire('%env(int:PASSWORD_HASH_COST)%')]
        private int $cost
    ) {}

    #[Override]
    public function hash(string $data): string
    {
        /**
         * @var non-empty-string $hash
         */
        $hash = password_hash(
            password: $data,
            algo: PASSWORD_BCRYPT,
            options: ['cost' => $this->cost]
        );

        return $hash;
    }

    #[Override]
    public function verify(string $data, string $hash): bool
    {
        return password_verify($data, $hash);
    }
}
