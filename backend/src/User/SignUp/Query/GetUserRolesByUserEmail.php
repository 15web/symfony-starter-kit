<?php

declare(strict_types=1);

namespace App\User\SignUp\Query;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\UserProvider\GetSecurityUserRoles;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

#[AsService]
final class GetUserRolesByUserEmail implements GetSecurityUserRoles
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<string>
     */
    public function __invoke(string $userEmail): array
    {
        Assert::notEmpty($userEmail);
        Assert::email($userEmail);

        $dql = <<<'DQL'
                SELECT u.userRole
                FROM App\User\SignUp\Domain\User AS u
                WHERE u.userEmail.value = :userEmail
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userEmail', $userEmail);

        /** @var array<string> $roles */
        $roles = [$dqlQuery->getSingleScalarResult()];

        Assert::allString($roles);

        return $roles;
    }
}
