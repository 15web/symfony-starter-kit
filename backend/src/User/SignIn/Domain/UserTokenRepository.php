<?php

declare(strict_types=1);

namespace App\User\SignIn\Domain;

use App\Infrastructure\AsService;
use App\User\User\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий токенов пользователя
 */
#[AsService]
final readonly class UserTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function getById(Uuid $userTokenId): UserToken
    {
        $userToken = $this->entityManager->getRepository(UserToken::class)->find($userTokenId);
        if (!$userToken instanceof UserToken) {
            throw new DomainException('Токен пользователя не найден.');
        }

        return $userToken;
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
