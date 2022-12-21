<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\RecoveryPassword\Domain\RecoveryToken;
use App\User\RecoveryPassword\Notification\RecoverPasswordMessage;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserNotFoundException;
use App\User\SignUp\Domain\Users;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class SendRecoverPassword
{
    public function __construct(
        private readonly Users $users,
        private readonly Flush $flush,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(SendRecoverPasswordCommand $sendRecoverPasswordCommand): void
    {
        /** @var ?User $user */
        $user = $this->users->findByEmail($sendRecoverPasswordCommand->email);

        if ($user === null) {
            throw new UserNotFoundException('Пользователь с таким email не найден');
        }

        $recoveryToken = new RecoveryToken();
        $user->updateRecoveryToken($recoveryToken);

        ($this->flush)();

        $this->messageBus->dispatch(new RecoverPasswordMessage($recoveryToken, $sendRecoverPasswordCommand->email));
    }
}
