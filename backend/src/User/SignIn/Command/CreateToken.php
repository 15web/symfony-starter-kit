<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\User\User\Domain\AuthToken;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserToken;
use App\User\User\Domain\UserTokenRepository;

/**
 * Хендлер создания токена
 */
final readonly class CreateToken
{
    public function __construct(
        private UserTokenRepository $userTokenRepository,
    ) {}

    public function __invoke(UserId $userId, AuthToken $token): void
    {
        $userToken = new UserToken(
            id: $token->tokenId,
            userId: $userId,
            hash: $token->hash(),
        );

        $this->userTokenRepository->add($userToken);
    }
}
