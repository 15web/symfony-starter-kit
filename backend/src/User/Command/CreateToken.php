<?php

declare(strict_types=1);

namespace App\User\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\Domain\UserId;
use App\User\Domain\Users;
use App\User\Domain\UserToken;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class CreateToken
{
    public function __construct(private readonly Users $users, private readonly Flush $flush)
    {
    }

    public function __invoke(UserId $userId, Uuid $userTokenId): void
    {
        $user = $this->users->getById($userId);

        $user->addToken(new UserToken($userTokenId, $user));

        ($this->flush)();
    }
}
