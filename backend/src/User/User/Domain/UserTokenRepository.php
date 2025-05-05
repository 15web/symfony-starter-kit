<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий токенов пользователя
 */
final readonly class UserTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function findById(UserTokenId $userTokenId): ?UserToken
    {
        return $this->entityManager
            ->getRepository(UserToken::class)
            ->find($userTokenId->value);
    }

    public function remove(UserToken $userToken): void
    {
        $this->entityManager->remove($userToken);
    }

    public function add(UserToken $userToken): void
    {
        $this->entityManager->persist($userToken);
    }

    public function removeAllByUserId(UserId $userId): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(UserToken::class, 't')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId->value)
            ->getQuery()
            ->execute();
    }
}
