<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokenRepository;
use App\User\SignIn\Service\AuthTokenHasher;
use App\User\User\Domain\UserId;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * Хендлер создания токена
 */
#[AsService]
final readonly class CreateToken
{
    public function __construct(
        private UserTokenRepository $userTokenRepository,
        #[Autowire(service: AuthTokenHasher::class)]
        private Hasher $hasher
    ) {}

    /**
     * @param non-empty-string $token
     */
    public function __invoke(UserId $userId, Uuid $userTokenId, string $token): void
    {
        $userToken = new UserToken(
            id: $userTokenId,
            userId: $userId,
            hash: $this->hasher->hash($token)
        );
        $this->userTokenRepository->add($userToken);
    }
}
