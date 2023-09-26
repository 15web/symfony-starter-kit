<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\Infrastructure\AsService;
use App\Infrastructure\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий пользователей
 */
#[AsService]
final readonly class Users
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @throws DomainException
     */
    public function getById(UserId $userId): User
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId->value);
        if (!$user instanceof User) {
            throw new DomainException('Пользователь не найден.');
        }

        return $user;
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy([
            'userEmail.value' => $email->value,
        ]);
    }

    public function findByConfirmToken(Uuid $confirmToken): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'confirmToken.value' => $confirmToken,
        ]);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }
}
