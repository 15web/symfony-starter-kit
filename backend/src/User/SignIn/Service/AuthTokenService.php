<?php

declare(strict_types=1);

namespace App\User\SignIn\Service;

use App\Infrastructure\AsService;
use App\User\User\Http\TokenException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * Сервис для работы с токенами аутентификации
 */
#[AsService]
final readonly class AuthTokenService
{
    private const string SEPARATOR = '-';

    public function generateAuthToken(): AuthToken
    {
        return new AuthToken(
            tokenId: new UuidV7(),
            token: md5(
                uniqid(
                    prefix: (string) time(),
                    more_entropy: true
                )
            )
        );
    }

    /**
     * @return non-empty-string
     */
    public function buildConcatenatedToken(
        AuthToken $authToken
    ): string {
        return sprintf(
            '%s%s%s',
            $authToken->tokenId,
            self::SEPARATOR,
            $authToken->token
        );
    }

    /**
     * @param non-empty-string $token
     */
    public function parseToken(string $token): AuthToken
    {
        $this->validateToken($token);

        $separatedToken = explode(
            self::SEPARATOR,
            $token
        );

        /**
         * @var non-empty-string $authToken
         */
        $authToken = array_pop($separatedToken);
        $tokenId = implode(
            self::SEPARATOR,
            $separatedToken
        );

        return new AuthToken(
            tokenId: Uuid::fromString($tokenId),
            token: $authToken
        );
    }

    private function validateToken(string $token): void
    {
        $regexPattern = sprintf(
            '/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}\%s.*/',
            self::SEPARATOR
        );

        $pregMatch = (bool) preg_match(
            pattern: $regexPattern,
            subject: $token
        );

        if (!$pregMatch) {
            throw new TokenException('Невалидный токен');
        }
    }
}
