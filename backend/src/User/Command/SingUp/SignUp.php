<?php

declare(strict_types=1);

namespace App\User\Command\SingUp;

use App\Infrastructure\Flusher;
use App\User\Model\User;
use App\User\Model\UserEmail;
use App\User\Model\Users;
use App\User\Notification\NewPasswordMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

final class SignUp
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly Flusher $flusher,
        private readonly Users $users,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(SignUpCommand $signUpCommand): void
    {
        $user = $this->users->findByEmail($signUpCommand->email);
        if ($user !== null) {
            throw new UserAlreadyExistException('Пользователь с таким email уже существует');
        }

        $userEmail = new UserEmail($signUpCommand->email);

        $user = new User($userEmail);

        $plaintextPassword = ByteString::fromRandom(10)->toString();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $user->applyPassword($hashedPassword);

        $this->users->add($user);
        $this->flusher->flush();

        $this->messageBus->dispatch(new NewPasswordMessage($plaintextPassword, $userEmail->getValue()));
    }
}
