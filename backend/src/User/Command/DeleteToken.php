<?php

declare(strict_types=1);

namespace App\User\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\Domain\Users;
use App\User\Domain\UserTokens;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class DeleteToken
{
    public function __construct(
        private readonly Users $users,
        private readonly UserTokens $userTokens,
        private readonly Flush $flush,
    ) {
    }

    public function __invoke(Uuid $userId, Uuid $userTokenId): void
    {
        $user = $this->users->getById($userId);
        $userToken = $this->userTokens->getById($userTokenId);

        $user->removeToken($userToken);

        ($this->flush)();
    }
}
