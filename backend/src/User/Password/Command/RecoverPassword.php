<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Infrastructure\AsService;
use App\User\Password\Domain\RecoveryTokenRepository;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use App\User\User\Domain\UserTokenRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

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
        private RecoveryTokenRepository $recoveryTokenRepository,
        private UserTokenRepository $userTokenRepository,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(
        Uuid $recoveryToken,
        RecoverPasswordCommand $recoverPasswordCommand,
    ): void {
        $token = $this->recoveryTokenRepository->findByToken($recoveryToken);

        if ($token === null) {
            throw new RecoveryTokenNotFoundException();
        }

        $user = $this->userRepository->getById($token->getUserId());

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $recoverPasswordCommand->password,
                hashCost: $this->hashCost,
            ),
        );

        $this->recoveryTokenRepository->remove($token);

        $this->userTokenRepository->removeAllByUserId($token->getUserId());
    }
}
