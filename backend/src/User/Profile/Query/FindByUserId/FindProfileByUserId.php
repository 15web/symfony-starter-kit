<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения профиля по пользователю
 */
#[AsService]
final class FindProfileByUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(FindProfileByUserIdQuery $query): ProfileData
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\User\Profile\Query\FindByUserId\ProfileData(p.phone.value, p.name)
                FROM App\User\Profile\Domain\Profile AS p
                WHERE p.userId = :userId
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('userId', $query->userId->toBinary());

        /** @var ?ProfileData $result */
        $result = $dqlQuery->getOneOrNullResult();

        if ($result === null) {
            return new ProfileData();
        }

        return $result;
    }
}
