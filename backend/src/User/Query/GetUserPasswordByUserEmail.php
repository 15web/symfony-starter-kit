<?php

declare(strict_types=1);

namespace App\User\Query;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\UserProvider\GetSecurityUserPassword;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;

#[AsService]
final class GetUserPasswordByUserEmail implements GetSecurityUserPassword
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(string $userEmail): ?string
    {
        $dql = <<<'DQL'
                SELECT u.userPassword.value
                FROM App\User\Domain\User AS u
                WHERE u.userEmail.value = :userEmail
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userEmail', $userEmail);

        try {
            /** @var ?string $userPassword */
            $userPassword = $dqlQuery->getSingleScalarResult();
        } catch (NoResultException) {
            $userPassword = null;
        }

        return $userPassword;
    }
}
