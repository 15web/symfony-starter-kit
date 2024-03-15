<?php

declare(strict_types=1);

namespace App\User\User\Http;

use App\Infrastructure\AsService;
use App\Infrastructure\Hasher;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokenRepository;
use App\User\SignIn\Service\AuthTokenHasher;
use App\User\SignIn\Service\AuthTokenService;
use DomainException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Хранилище токена пользователя, используется совместно с атрибутом IsGranted.
 */
#[AsService]
final readonly class TokenManager
{
    public const string TOKEN_NAME = 'X-AUTH-TOKEN';

    public function __construct(
        private UserTokenRepository $userTokenRepository,
        private AuthTokenService $authTokenService,
        #[Autowire(service: AuthTokenHasher::class)]
        private Hasher $hasher
    ) {}

    public function getToken(Request $request): UserToken
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);

        if ($apiToken === null) {
            throw new TokenException('Не передан токен');
        }

        /**
         * @var non-empty-string $apiToken
         */
        $authToken = $this->authTokenService->parseToken($apiToken);

        try {
            $userToken = $this->userTokenRepository->getById($authToken->tokenId);
        } catch (DomainException) {
            throw new TokenException('Токен не найден');
        }

        if (
            !$this->hasher->verify(
                data: $authToken->token,
                hash: $userToken->getHash()
            )
        ) {
            throw new TokenException('Токен не найден');
        }

        return $userToken;
    }
}
