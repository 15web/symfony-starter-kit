<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\User\User\Domain\Exception\EmailAlreadyIsConfirmedException;
use App\User\User\Domain\Exception\UserNotFoundException;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRepository;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер подтверждения email
 */
final readonly class ConfirmEmail
{
    public function __construct(
        private FindUser $findUser,
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws EmailAlreadyIsConfirmedException|UserNotFoundException
     */
    public function __invoke(
        #[SensitiveParameter]
        Uuid $confirmToken,
    ): void {
        $userData = ($this->findUser)(
            new FindUserQuery(confirmToken: $confirmToken)
        );

        if ($userData === null) {
            throw new UserNotFoundException();
        }

        $user = $this->userRepository->findById(new UserId($userData->userId));

        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->confirm();
    }
}
