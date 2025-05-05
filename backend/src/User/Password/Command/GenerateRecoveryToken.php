<?php

declare(strict_types=1);

namespace App\User\Password\Command;

use App\Mailer\Notification\PasswordRecovery\RecoveryPasswordMessage;
use App\User\Password\Domain\RecoveryToken;
use App\User\Password\Domain\RecoveryTokenRepository;
use App\User\User\Domain\Exception\UserNotFoundException;
use App\User\User\Domain\UserId;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * Создает токен восстановления пароля
 */
final readonly class GenerateRecoveryToken
{
    public function __construct(
        private RecoveryTokenRepository $recoveryTokenRepository,
        private MessageBusInterface $messageBus,
        private FindUser $findUser,
    ) {}

    public function __invoke(GenerateRecoveryTokenCommand $command): void
    {
        $userData = ($this->findUser)(
            new FindUserQuery(userEmail: $command->email)
        );

        if ($userData === null) {
            throw new UserNotFoundException('Пользователь с таким email не найден');
        }

        $recoveryToken = new RecoveryToken(
            id: new UuidV7(),
            userId: new UserId($userData->userId),
            token: new UuidV7(),
        );

        $this->recoveryTokenRepository->add($recoveryToken);

        $this->messageBus->dispatch(
            message: new RecoveryPasswordMessage(
                token: $recoveryToken->getToken(),
                email: $command->email,
            ),
        );
    }
}
