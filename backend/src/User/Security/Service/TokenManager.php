<?php

declare(strict_types=1);

namespace App\User\Security\Service;

use App\Infrastructure\AsService;
use App\User\User\Domain\AuthToken;
use App\User\User\Domain\UserToken;
use App\User\User\Domain\UserTokenRepository;
use DomainException;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Хранилище токена пользователя, используется совместно с атрибутом IsGranted.
 */
#[AsService]
final readonly class TokenManager
{
    public const string TOKEN_NAME = 'X-AUTH-TOKEN';

    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private UserTokenRepository $userTokenRepository,
    ) {}

    public function getToken(Request $request): UserToken
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);

        if ($apiToken === null) {
            throw new TokenException('Не передан токен');
        }

        try {
            /** @var non-empty-string $apiToken */
            $authToken = AuthToken::createFromString(
                token: $apiToken,
                hashCost: $this->hashCost,
            );
        } catch (InvalidArgumentException) {
            throw new TokenException('Токен не найден');
        }

        try {
            $userToken = $this->userTokenRepository->getById($authToken->tokenId);
        } catch (DomainException) {
            throw new TokenException('Токен не найден');
        }

        if (!$authToken->verify($userToken)) {
            throw new TokenException('Токен не найден');
        }

        return $userToken;
    }
}
