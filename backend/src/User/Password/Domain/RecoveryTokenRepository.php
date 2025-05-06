<?php

declare(strict_types=1);

namespace App\User\Password\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий паролей восстановления
 */
final readonly class RecoveryTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function findByToken(Uuid $token): ?RecoveryToken
    {
        return $this->entityManager->getRepository(RecoveryToken::class)
            ->findOneBy(['token' => $token]);
    }

    public function add(RecoveryToken $recoveryToken): void
    {
        $this->entityManager->persist($recoveryToken);
    }

    public function remove(RecoveryToken $recoveryToken): void
    {
        $this->entityManager->remove($recoveryToken);
    }
}
