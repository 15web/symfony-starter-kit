<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\Mailer\Notification\EmailConfirmation\ConfirmEmailMessage;
use App\User\SignUp\Domain\EmailIsNotConfirmedException;
use App\User\SignUp\Domain\Users;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Хендлер логина
 */
#[AsService]
final readonly class SignIn
{
    public function __construct(
        private Users $users,
        private CreateToken $createToken,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SignInCommand $signInCommand): void
    {
        $user = $this->users->findByEmail($signInCommand->email);

        if ($user === null) {
            throw new DomainException('Некорректный email');
        }

        if (!password_verify($signInCommand->password, $user->getPassword())) {
            throw new DomainException('Неправильные логин/пароль');
        }

        if (!$user->isConfirmed()) {
            $this->messageBus->dispatch(
                new ConfirmEmailMessage(
                    confirmToken: $user->confirmToken->value,
                    email: $user->userEmail,
                ),
            );

            throw new EmailIsNotConfirmedException();
        }

        ($this->createToken)(
            userId: $user->getUserId(),
            userTokenId: $signInCommand->token,
        );

        $this->logger->info('Пользователь залогинен', [
            'userId' => $user->getUserId(),
            self::class => __FUNCTION__,
        ]);
    }
}
