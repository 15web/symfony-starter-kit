<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\AsService;
use App\User\SignUp\Domain\EmailAlreadyIsConfirmedException;
use App\User\SignUp\Domain\UserNotFoundException;
use App\User\SignUp\Domain\Users;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер подтверждения email
 */
#[AsService]
final readonly class ConfirmEmail
{
    public function __construct(private Users $users)
    {
    }

    /**
     * @throws EmailAlreadyIsConfirmedException|UserNotFoundException
     */
    public function __invoke(Uuid $confirmToken): void
    {
        $user = $this->users->findByConfirmToken($confirmToken);
        $user->confirm();
    }
}
