<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Infrastructure\AsService;
use App\User\Password\Domain\RecoveryToken;
use App\User\User\Domain\Exception\UserNotFoundException;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Восстанавливает пароль
 */
#[AsService]
final readonly class RecoverPassword
{
    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(
        RecoveryToken $recoveryToken,
        RecoverPasswordCommand $recoverPasswordCommand,
    ): void {
        $user = $this->userRepository->findById($recoveryToken->getUserId());

        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $recoverPasswordCommand->password,
                hashCost: $this->hashCost,
            ),
        );
    }
}
