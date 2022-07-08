<?php

declare(strict_types=1);

namespace App\User\Command;

use App\Infrastructure\Flusher;
use App\User\Model\User;
use App\User\Model\UserToken;
use App\User\Model\UserTokens;

final class CreateToken
{
    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly Flusher $flusher,
    ) {
    }

    public function __invoke(User $user): UserToken
    {
        $token = new UserToken($user);

        $this->userTokens->add($token);

        $this->flusher->flush();

        return $token;
    }
}
