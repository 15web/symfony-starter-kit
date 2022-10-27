<?php

declare(strict_types=1);

namespace App\User\SignIn\Query;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\Authenticator\ApiToken\IsTokenExists;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class FindUserTokenById implements IsTokenExists
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    // todo нейминг $userTokenId с интерфейсом не сходится $userTokenId
    public function __invoke(Uuid $userTokenId): bool
    {
        $dql = <<<'DQL'
                SELECT ut.id
                FROM App\User\SignIn\Domain\UserToken AS ut
                WHERE ut.id = :userTokenId
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userTokenId', $userTokenId->toBinary());

        $userToken = $dqlQuery->getOneOrNullResult();

        return $userToken !== null;
    }
}
