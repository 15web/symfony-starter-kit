<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use App\Infrastructure\AsService;
use App\User\SignUp\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий профилей
 */
#[AsService]
final readonly class Profiles
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByUserId(UserId $userId): ?Profile
    {
        return $this->entityManager->getRepository(Profile::class)
            ->findOneBy(['userId' => $userId->value]);
    }

    public function add(Profile $task): void
    {
        $this->entityManager->persist($task);
    }
}
