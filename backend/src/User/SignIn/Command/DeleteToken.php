<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\User\User\Domain\UserTokenId;
use App\User\User\Domain\UserTokenRepository;

/**
 * Хендлер удаления токена
 */
#[AsService]
final readonly class DeleteToken
{
    public function __construct(private UserTokenRepository $userTokenRepository) {}

    public function __invoke(UserTokenId $userTokenId): void
    {
        $userToken = $this->userTokenRepository->getById($userTokenId);

        $this->userTokenRepository->remove($userToken);
    }
}
