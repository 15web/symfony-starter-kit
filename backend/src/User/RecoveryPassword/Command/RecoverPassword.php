<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use App\User\RecoveryPassword\Domain\RecoveryTokenRepository;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use Symfony\Component\Uid\Uuid;

/**
 * Восстанавливает пароль
 */
#[AsService]
final readonly class RecoverPassword
{
    public function __construct(
        private RecoveryTokenRepository $recoveryTokenRepository,
        private UserRepository $userRepository,
        private Hasher $passwordHasher
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

        /** @var non-empty-string $hashedPassword */
        $hashedPassword = $this->passwordHasher->hash($recoverPasswordCommand->password);

        $user->applyHashedPassword(new UserPassword($hashedPassword));
    }
}
