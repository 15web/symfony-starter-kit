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
use App\User\SignUp\Notification\NewPasswordMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

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

        $userEmail = new UserEmail($signUpCommand->email);
        $user = new User(new UserId(), $userEmail, UserRole::User);

        $plaintextPassword = ByteString::fromRandom(10)->toString();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $this->users->add($user);
        ($this->flush)();

        $this->messageBus->dispatch(new NewPasswordMessage($plaintextPassword, $userEmail->value));
    }
}
