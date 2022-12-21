<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\RecoveryPassword\Domain\RecoveryToken;
use App\User\SignUp\Domain\UserNotFoundException;
use App\User\SignUp\Domain\UserPassword;
use App\User\SignUp\Domain\Users;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsService]
final class RecoverPassword
{
    public function __construct(
        private readonly Users $users,
        private readonly Flush $flush,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(RecoverPasswordCommand $recoverPasswordCommand): void
    {
        $user = $this->users->findByRecoverToken($recoverPasswordCommand->recoverToken);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $recoverPasswordCommand->password
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $recoveryToken = new RecoveryToken(null);
        $user->updateRecoveryToken($recoveryToken);
        ($this->flush)();
    }
}
