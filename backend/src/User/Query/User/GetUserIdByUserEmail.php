<?php

declare(strict_types=1);

namespace App\User\Query\User;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\UserProvider\GetSecurityUserId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[AsService]
final class GetUserIdByUserEmail implements GetSecurityUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(string $userEmail): Uuid
    {
        Assert::notEmpty($userEmail);
        Assert::email($userEmail);

        $dql = <<<'DQL'
                SELECT u.id
                FROM App\User\Domain\User AS u
                WHERE u.userEmail.value = :userEmail
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userEmail', $userEmail);

        /** @var string $userId */
        $userId = $dqlQuery->getSingleScalarResult();

        return Uuid::fromString($userId);
    }
}
