<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\ValueObject\Email;
use App\Mailer\Notification\EmailConfirmation\ConfirmEmailMessage;
use App\User\User\Domain\Exception\EmailIsNotConfirmedException;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserPassword;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Хендлер логина
 */
final readonly class SignIn
{
    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private CreateToken $createToken,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private FindUser $findUser,
    ) {}

    public function __invoke(SignInCommand $signInCommand): void
    {
        $userData = ($this->findUser)(
            new FindUserQuery(userEmail: $signInCommand->email)
        );

        if ($userData === null) {
            throw new DomainException('Некорректный email');
        }

        $userPassword = new UserPassword(
            cleanPassword: $signInCommand->password,
            hashCost: $this->hashCost,
        );

        if (!$userPassword->verify($userData->password)) {
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
            token: $signInCommand->authToken,
        );

        $this->logger->info('Пользователь авторизован', [
            'userId' => $userData->userId,
            self::class => __FUNCTION__,
        ]);
    }
}
