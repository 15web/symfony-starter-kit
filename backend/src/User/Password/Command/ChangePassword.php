<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\User\User\Domain\Exception\UserNotFoundException;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Команда смены текущего пароля
 */
final readonly class ChangePassword
{
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $command->newPassword,
                hashCost: $this->hashCost,
            ),
        );
    }
}
