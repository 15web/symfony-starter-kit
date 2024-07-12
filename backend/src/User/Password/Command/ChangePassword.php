<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Infrastructure\AsService;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Команда смены текущего пароля
 */
#[AsService]
final readonly class ChangePassword
{
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $command->newPassword,
                hashCost: $this->hashCost,
            ),
        );
    }
}
