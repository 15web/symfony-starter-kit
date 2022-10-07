<?php

declare(strict_types=1);

namespace App\User\Query\User;

use App\Infrastructure\AsService;
use App\User\Query\User\Model\User;
use Doctrine\ORM\EntityManagerInterface;

#[AsService]
final class FindAllUsers
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<User>
     */
    public function __invoke(): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\User\Query\User\Model\User(u.id, u.userEmail.value)
                FROM App\User\Domain\User as u
            DQL;

        /**
         * @var array<User> $allUsers
         */
        $allUsers = $this->entityManager->createQuery($dql)->getResult();

        return $allUsers;
    }
}
