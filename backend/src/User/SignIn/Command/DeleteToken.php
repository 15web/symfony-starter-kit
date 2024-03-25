<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\User\SignIn\Domain\UserTokenRepository;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер удаления токена
 */
#[AsService]
final readonly class DeleteToken
{
    public function __construct(private UserTokenRepository $userTokenRepository) {}

    public function __invoke(Uuid $userTokenId): void
    {
        $userToken = $this->userTokenRepository->getById($userTokenId);

        $this->userTokenRepository->remove($userToken);
    }
}
