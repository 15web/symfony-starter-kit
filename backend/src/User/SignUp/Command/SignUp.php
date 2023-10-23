<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\AsService;
use App\Mailer\Notification\EmailConfirmation\ConfirmEmailMessage;
use App\User\SignUp\Domain\ConfirmToken;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserPassword;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Domain\Users;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * Хендлер регистрации
 */
#[AsService]
final readonly class SignUp
{
    public function __construct(
        private Users $users,
        private MessageBusInterface $messageBus,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(SignUpCommand $signUpCommand): void
    {
        $user = $this->users->findByEmail($signUpCommand->email);
        if ($user !== null) {
            throw new UserAlreadyExistException('Пользователь с таким email уже существует');
        }

        $confirmToken = new UuidV7();
        $user = new User(
            userId: new UserId(),
            userEmail: $signUpCommand->email,
            confirmToken: new ConfirmToken($confirmToken),
            userRole: UserRole::User,
        );

        /**
         * @var non-empty-string $hashedPassword
         */
        $hashedPassword = $this->passwordHasher->hashPassword(
            user: $user,
            plainPassword: $signUpCommand->password,
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $this->users->add($user);

        $this->messageBus->dispatch(
            new ConfirmEmailMessage(
                confirmToken: $confirmToken,
                email: $signUpCommand->email,
            ),
        );
    }
}
