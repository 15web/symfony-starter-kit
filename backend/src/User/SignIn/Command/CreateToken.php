<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\User\User\Domain\AuthToken;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserToken;
use App\User\User\Domain\UserTokenId;
use App\User\User\Domain\UserTokenRepository;

/**
 * Хендлер создания токена
 */
#[AsService]
final readonly class CreateToken
{
    public function __construct(
        private UserTokenRepository $userTokenRepository,
    ) {}

    public function __invoke(UserId $userId, UserTokenId $userTokenId, AuthToken $token): void
    {
        $userToken = new UserToken(
            id: $userTokenId,
            userId: $userId,
            hash: $token->hash(),
        );

        $this->userTokenRepository->add($userToken);
    }
}
