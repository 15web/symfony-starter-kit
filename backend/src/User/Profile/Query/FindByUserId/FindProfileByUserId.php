<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения профиля по пользователю
 */
final readonly class FindProfileByUserId
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function __invoke(FindProfileByUserIdQuery $query): ProfileData
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\User\Profile\Query\FindByUserId\ProfileData(p.phone.value, p.name)
                FROM App\User\Profile\Domain\Profile AS p
                WHERE p.userId = :userId
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter(
            key: 'userId',
            value: $query->userId,
        );

        /** @var ?ProfileData $result */
        $result = $dqlQuery->getOneOrNullResult();

        return $result ?? new ProfileData();
    }
}
