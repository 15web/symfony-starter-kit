<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий пользователей
 */
final readonly class UserRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function findById(UserId $userId): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->find($userId->value);
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }
}
