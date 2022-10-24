<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserEmail;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserPassword;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Domain\Users;
use App\User\SignUp\Notification\ConfirmEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class SignUp
{
    public function __construct(
        private readonly Users $users,
        private readonly Flush $flush,
        private readonly MessageBusInterface $messageBus,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(SignUpCommand $signUpCommand): void
    {
        $user = $this->users->findByEmail($signUpCommand->email);
        if ($user !== null) {
            throw new UserAlreadyExistException('Пользователь с таким email уже существует');
        }

        $confirmToken = Uuid::v4();
        $userEmail = new UserEmail($signUpCommand->email, $confirmToken);
        $user = new User(new UserId(), $userEmail, UserRole::User);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $signUpCommand->password
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $this->users->add($user);
        ($this->flush)();

        $this->messageBus->dispatch(new ConfirmEmailMessage($confirmToken, $userEmail->value));
    }
}
