<?php

declare(strict_types=1);

namespace App\User\User\Query;

use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Возвращает данные пользоватля по его id или email
 */
final readonly class FindUser
{
    public function __construct(
        private Connection $connection,
        private DenormalizerInterface $denormalizer,
    ) {}

    public function __invoke(FindUserQuery $query): ?UserData
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(
                'id as "userId"',
                'user_email_value as email',
                'user_role as role',
                'user_password_value as password',
                'is_confirmed as "isConfirmed"',
                'confirm_token_value as "confirmToken"',
            )
            ->from('"user"')
            ->setMaxResults(1);

        if ($query->userId !== null) {
            $queryBuilder
                ->andWhere('id = :id')
                ->setParameter('id', $query->userId->value);
        }

        if ($query->userEmail !== null) {
            $queryBuilder
                ->andWhere('user_email_value = :email')
                ->setParameter('email', $query->userEmail->value);
        }

        if ($query->confirmToken !== null) {
            $queryBuilder
                ->andWhere('confirm_token_value = :confirmToken')
                ->setParameter('confirmToken', $query->confirmToken);
        }

        $item = $queryBuilder->executeQuery()->fetchAssociative();

        if ($item === false) {
            return null;
        }

        /** @var UserData $userData */
        $userData = $this->denormalizer->denormalize(
            data: $item,
            type: UserData::class,
            context: [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ],
        );

        return $userData;
    }
}
