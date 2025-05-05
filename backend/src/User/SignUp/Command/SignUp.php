<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Mailer\Notification\EmailConfirmation\ConfirmEmailMessage;
use App\User\User\Domain\ConfirmToken;
use App\User\User\Domain\Exception\UserAlreadyExistException;
use App\User\User\Domain\User;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRepository;
use App\User\User\Domain\UserRole;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * Хендлер регистрации
 */
final readonly class SignUp
{
    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private UserRepository $userRepository,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private FindUser $findUser,
    ) {}

    public function __invoke(SignUpCommand $signUpCommand): void
    {
        $userData = ($this->findUser)(
            new FindUserQuery(userEmail: $signUpCommand->email)
        );

        if ($userData !== null) {
            throw new UserAlreadyExistException('Пользователь с таким email уже существует');
        }

        $userId = new UserId();
        $confirmToken = new UuidV7();

        $user = new User(
            userId: $userId,
            userEmail: $signUpCommand->email,
            confirmToken: new ConfirmToken($confirmToken),
            userRole: UserRole::User,
        );

        $user->applyPassword(
            new UserPassword(
                cleanPassword: $signUpCommand->password,
                hashCost: $this->hashCost,
            ),
        );

        $this->userRepository->add($user);

        $this->messageBus->dispatch(
            new ConfirmEmailMessage(
                confirmToken: $confirmToken,
                email: $signUpCommand->email,
            ),
        );

        $this->logger->info('Пользователь зарегистрирован', [
            'userId' => $userId->value,
            self::class => __FUNCTION__,
        ]);
    }
}
