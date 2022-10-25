<?php

declare(strict_types=1);

namespace App\User\Query\FindAllUsers;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class FindAllUsers
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<UserData>
     */
    public function __invoke(): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\User\Query\FindAllUsers\UserData(u.id, u.userEmail.value)
                FROM App\User\Domain\User as u
            DQL;

        /**
         * @var array<UserData> $allUsers
         */
        $allUsers = $this->entityManager->createQuery($dql)->getResult();

        return $allUsers;
    }
}
