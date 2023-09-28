<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\User\RecoveryPassword\Domain\RecoveryTokens;
use App\User\SignUp\Domain\UserPassword;
use App\User\SignUp\Domain\Users;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Восстанавливает пароль
 */
#[AsService]
final readonly class RecoverPassword
{
    public function __construct(
        private RecoveryTokens $recoveryTokens,
        private Users $users,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(
        Uuid $recoveryToken,
        RecoverPasswordCommand $recoverPasswordCommand,
    ): void {
        $token = $this->recoveryTokens->findByToken($recoveryToken);

        if ($token === null) {
            throw new RecoveryTokenNotFoundException();
        }

        $user = $this->users->getById($token->getUserId());

        /**
         * @var non-empty-string $hashedPassword
         */
        $hashedPassword = $this->passwordHasher->hashPassword(
            user: $user,
            plainPassword: $recoverPasswordCommand->password
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));
    }
}
