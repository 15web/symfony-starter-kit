<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\SignIn\Domain\UserTokens;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер удаления токена
 */
#[AsService]
final class DeleteToken
{
    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly Flush $flush,
    ) {
    }

    public function __invoke(Uuid $userTokenId): void
    {
        $userToken = $this->userTokens->getById($userTokenId);

        $this->userTokens->remove($userToken);

        ($this->flush)();
    }
}
