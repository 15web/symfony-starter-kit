<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий паролей восстановления
 */
#[AsService]
final readonly class RecoveryTokens
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByToken(Uuid $token): ?RecoveryToken
    {
        return $this->entityManager->getRepository(RecoveryToken::class)
            ->findOneBy(['token' => $token]);
    }

    public function add(RecoveryToken $recoveryToken): void
    {
        $this->entityManager->persist($recoveryToken);
    }
}
