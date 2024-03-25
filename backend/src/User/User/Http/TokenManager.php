<?php

declare(strict_types=1);

namespace App\User\User\Http;

use App\Infrastructure\AsService;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokenRepository;
use DomainException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * Хранилище токена пользователя, используется совместно с атрибутом IsGranted.
 */
#[AsService]
final readonly class TokenManager
{
    public const string TOKEN_NAME = 'X-AUTH-TOKEN';

    public function __construct(
        private UserTokenRepository $userTokenRepository,
    ) {}

    public function getToken(Request $request): UserToken
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);

        if ($apiToken === null) {
            throw new TokenException('Не передан токен');
        }

        if (!Uuid::isValid($apiToken)) {
            throw new TokenException('Невалидный токен');
        }

        try {
            $userToken = $this->userTokenRepository->getById(Uuid::fromString($apiToken));
        } catch (DomainException) {
            throw new TokenException('Токен не найден');
        }

        return $userToken;
    }
}
