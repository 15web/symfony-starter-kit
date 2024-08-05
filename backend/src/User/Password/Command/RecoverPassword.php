<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Infrastructure\AsService;
use App\User\Password\Domain\RecoveryToken;
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

    public function __invoke(
        RecoveryToken $recoveryToken,
        RecoverPasswordCommand $recoverPasswordCommand,
    ): void {
        $user = $this->userRepository->getById($recoveryToken->getUserId());

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $recoverPasswordCommand->password,
                hashCost: $this->hashCost,
            ),
        );
    }
}
