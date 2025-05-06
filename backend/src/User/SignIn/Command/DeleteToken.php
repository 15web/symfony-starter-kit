<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\User\Security\Service\TokenException;
use App\User\User\Domain\UserTokenId;
use App\User\User\Domain\UserTokenRepository;

/**
 * Хендлер удаления токена
 */
final readonly class DeleteToken
{
    public function __construct(private UserTokenRepository $userTokenRepository) {}

    public function __invoke(UserTokenId $userTokenId): void
    {
        $userToken = $this->userTokenRepository->findById($userTokenId);

        if ($userToken === null) {
            throw new TokenException('Токен не найден');
        }

        $this->userTokenRepository->remove($userToken);
    }
}
