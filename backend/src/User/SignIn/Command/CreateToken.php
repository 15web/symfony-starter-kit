<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokens;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер создания токена
 */
#[AsService]
final class CreateToken
{
    public function __construct(private readonly UserTokens $userTokens)
    {
    }

    public function __invoke(UserId $userId, Uuid $userTokenId): void
    {
        $userToken = new UserToken($userTokenId, $userId);
        $this->userTokens->add($userToken);
    }
}
