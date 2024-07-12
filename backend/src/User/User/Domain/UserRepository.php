<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

/**
 * Репозиторий пользователей
 */
#[AsService]
final readonly class UserRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @throws DomainException
     *
     * @todo Переделать на метод find без выбрасывания исключения, сделать после #98076
     */
    public function getById(UserId $userId): User
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId->value);
        if (!$user instanceof User) {
            throw new DomainException('Пользователь не найден.');
        }

        return $user;
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }
}
