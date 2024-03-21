<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\User\RecoveryPassword\Domain\RecoveryTokens;
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
        private RecoveryTokens $recoveryTokens,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(
        Uuid $recoveryToken,
        RecoverPasswordCommand $recoverPasswordCommand,
    ): void {
        $token = $this->recoveryTokens->findByToken($recoveryToken);

        if ($token === null) {
            throw new RecoveryTokenNotFoundException();
        }

        $user = $this->userRepository->getById($token->getUserId());

        /** @var non-empty-string $hashedPassword */
        $hashedPassword = password_hash($recoverPasswordCommand->password, PASSWORD_DEFAULT);

        $user->applyHashedPassword(new UserPassword($hashedPassword));
    }
}
