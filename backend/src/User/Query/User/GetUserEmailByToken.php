<?php

declare(strict_types=1);

namespace App\User\Query\User;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\Authenticator\ApiToken\GetEmailByTokenId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[AsService]
final class GetUserEmailByToken implements GetEmailByTokenId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Uuid $tokenId): string
    {
        $dql = <<<'DQL'
                SELECT u.userEmail.value
                FROM App\User\Domain\UserToken AS ut
                INNER JOIN ut.user as u WITH u.id = ut.user
                WHERE ut.id = :tokenId
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('tokenId', $tokenId->toBinary());

        /** @var string $userEmail */
        $userEmail = $dqlQuery->getSingleScalarResult();

        Assert::string($userEmail);
        Assert::email($userEmail);

        return $userEmail;
    }
}
