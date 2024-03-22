<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokenRepository;
use App\User\User\Domain\UserId;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер создания токена
 */
#[AsService]
final readonly class CreateToken
{
    public function __construct(private UserTokenRepository $userTokenRepository) {}

    public function __invoke(UserId $userId, Uuid $userTokenId): void
    {
        $userToken = new UserToken($userTokenId, $userId);
        $this->userTokenRepository->add($userToken);
    }
}
