<?php

declare(strict_types=1);

namespace App\User\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\Domain\Users;
use App\User\Domain\UserToken;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class CreateToken
{
    public function __construct(private readonly Users $users, private readonly Flush $flush)
    {
    }

    public function __invoke(Uuid $userId): Uuid
    {
        $user = $this->users->getById($userId);

        $userTokenId = Uuid::v4();
        $user->addToken(new UserToken($userTokenId, $user));

        ($this->flush)();

        return $userTokenId;
    }
}
