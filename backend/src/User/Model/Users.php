<?php

declare(strict_types=1);

namespace App\User\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Webmozart\Assert\Assert;

final class Users
{
    /**
     * @var EntityRepository<User>
     */
    private EntityRepository $repository;

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
}
