<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class Users
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \DomainException
     */
    public function getById(UserId $userId): User
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId->value);
        if (!$user instanceof User) {
            throw new \DomainException('Пользователь не найден.');
        }

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy([
            'userEmail.value' => $email,
        ]);
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }
}
