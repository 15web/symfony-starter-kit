<?php

declare(strict_types=1);

namespace App\User\User\Query;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения всех пользователей
 */
final readonly class FindAllUsers
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @return array<UserListData>
     */
    public function __invoke(): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\User\User\Query\UserListData(u.id, u.userEmail.value)
                FROM App\User\User\Domain\User as u
            DQL;

        /**
         * @var array<UserListData> $allUsers
         */
        $allUsers = $this->entityManager->createQuery($dql)->getResult();

        return $allUsers;
    }
}
