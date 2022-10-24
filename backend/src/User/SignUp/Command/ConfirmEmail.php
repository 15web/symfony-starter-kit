<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\SignUp\Domain\EmailAlreadyIsConfirmedException;
use App\User\SignUp\Domain\UserNotFoundException;
use App\User\SignUp\Domain\Users;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class ConfirmEmail
{
    public function __construct(private readonly Users $users, private readonly Flush $flush)
    {
    }

    /**
     * @throws EmailAlreadyIsConfirmedException|UserNotFoundException
     */
    public function __invoke(Uuid $confirmToken): void
    {
        $user = $this->users->findByConfirmToken($confirmToken);
        $user->confirmEmail();

        ($this->flush)();
    }
}
