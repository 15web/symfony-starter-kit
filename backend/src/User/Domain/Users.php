<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Webmozart\Assert\Assert;

#[AsService]
final class Users
{
    /**
     * @var EntityRepository<User>
     */
    private readonly EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function findByEmail(string $email): ?User
    {
        Assert::notEmpty($email);
        Assert::email($email);

        return $this->repository->findOneBy(['userEmail.value' => $email]);
    }

    /**
     * @return User[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}
