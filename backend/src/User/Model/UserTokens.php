<?php

declare(strict_types=1);

namespace App\User\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

final class UserTokens
{
    /**
     * @var EntityRepository<UserToken>
     */
    private readonly EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(UserToken::class);
    }

    public function add(UserToken $userToken): void
    {
        $this->entityManager->persist($userToken);
    }

    public function getById(Uuid $id): UserToken
    {
        $token = $this->repository->find($id);
        if ($token === null) {
            throw new \DomainException('Токен не найден');
        }

        return $token;
    }
}
