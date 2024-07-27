<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use App\Infrastructure\ValueObject\Email;
use App\Mailer\Notification\EmailConfirmation\ConfirmEmailMessage;
use App\User\User\Domain\Exception\EmailIsNotConfirmedException;
use App\User\User\Domain\UserId;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
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
        private CreateToken $createToken,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private FindUser $findUser,
        private Hasher $hasher
    ) {}

    public function __invoke(SignInCommand $signInCommand): void
    {
        $userData = ($this->findUser)(
            new FindUserQuery(userEmail: $signInCommand->email)
        );

        if ($userData === null) {
            throw new DomainException('Некорректный email');
        }

        if (!$this->hasher->verify($signInCommand->password, $userData->password)) {
            throw new DomainException('Неправильные логин/пароль');
        }

        if (!$userData->isConfirmed) {
            if ($userData->confirmToken === null) {
                throw new DomainException('Некорректный токен подтверждения');
            }

            $this->messageBus->dispatch(
                new ConfirmEmailMessage(
                    confirmToken: $userData->confirmToken,
                    email: new Email($userData->email),
                ),
            );

            throw new EmailIsNotConfirmedException();
        }

        ($this->createToken)(
            userId: new UserId($userData->userId),
            userTokenId: $signInCommand->token,
        );

        $this->logger->info('Пользователь залогинен', [
            'userId' => $userData->userId,
            self::class => __FUNCTION__,
        ]);
    }
}
