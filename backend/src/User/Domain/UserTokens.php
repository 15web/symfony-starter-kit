<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class UserTokens
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getById(Uuid $userTokenId): UserToken
    {
        $userToken = $this->entityManager->getRepository(UserToken::class)->find($userTokenId);
        if (!$userToken instanceof UserToken) {
            throw new \DomainException('Токен пользователя не найден.');
        }

        return $userToken;
    }
}
