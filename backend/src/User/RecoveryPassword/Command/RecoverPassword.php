<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use App\User\RecoveryPassword\Domain\RecoveryTokenRepository;
use App\User\SignIn\Domain\UserTokenRepository;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * Восстанавливает пароль
 */
#[AsService]
final readonly class RecoverPassword
{
    public function __construct(
        private RecoveryTokenRepository $recoveryTokenRepository,
        private UserTokenRepository $userTokenRepository,
        private UserRepository $userRepository,
        #[Autowire(service: PasswordHasher::class)]
        private Hasher $hasher
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
        $hashedPassword = $this->hasher->hash($recoverPasswordCommand->password);

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $this->recoveryTokenRepository->remove($token);

        $this->userTokenRepository->removeAllByUserId($token->getUserId());
    }
}
