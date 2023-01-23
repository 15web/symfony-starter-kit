<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class Profiles
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findById(Uuid $id): ?Profile
    {
        return $this->entityManager->getRepository(Profile::class)->find($id);
    }
}
