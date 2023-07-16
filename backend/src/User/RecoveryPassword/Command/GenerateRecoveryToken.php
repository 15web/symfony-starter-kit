<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\Mailer\Notification\PasswordRecovery\RecoveryPasswordMessage;
use App\User\RecoveryPassword\Domain\RecoveryToken;
use App\User\RecoveryPassword\Domain\RecoveryTokens;
use App\User\SignUp\Domain\UserNotFoundException;
use App\User\SignUp\Domain\Users;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * Создает токен восстановления пароля
 */
#[AsService]
final readonly class GenerateRecoveryToken
{
    public function __construct(
        private Users $users,
        private RecoveryTokens $tokens,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(GenerateRecoveryTokenCommand $command): void
    {
        $user = $this->users->findByEmail($command->email);

        if ($user === null) {
            throw new UserNotFoundException('Пользователь с таким email не найден');
        }

        $recoveryToken = new RecoveryToken(new UuidV7(), $user->getUserId(), new UuidV7());

        $this->tokens->add($recoveryToken);

        $this->messageBus->dispatch(
            message: new RecoveryPasswordMessage(
                token: $recoveryToken->getToken(),
                email: $command->email,
            ),
        );
    }
}
