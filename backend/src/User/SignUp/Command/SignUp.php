<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Infrastructure\Email;
use App\Mailer\EmailConfirmation\Command\ConfirmEmailMessage;
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
    ) {
    }

    public function __invoke(#[ApiRequest] SignUpCommand $signUpCommand): void
    {
        $user = $this->users->findByEmail($signUpCommand->email);
        if ($user !== null) {
            throw new UserAlreadyExistException('user.exception.already_exist');
        }

        $confirmToken = new UuidV7();
        $userEmail = new Email($signUpCommand->email);
        $user = new User(new UserId(), $userEmail, new ConfirmToken($confirmToken), UserRole::User);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $signUpCommand->password
        );

        $user->applyHashedPassword(new UserPassword($hashedPassword));

        $this->users->add($user);

        $this->messageBus->dispatch(new ConfirmEmailMessage($confirmToken, $userEmail->value));
    }
}
