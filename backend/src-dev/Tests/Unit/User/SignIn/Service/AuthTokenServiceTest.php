<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\User\SignIn\Service;

use App\User\SignIn\Service\AuthToken;
use App\User\SignIn\Service\AuthTokenService;
use App\User\User\Http\TokenException;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * @internal
 */
#[TestDox('Сервис для работы с токенами аутентификации')]
final class AuthTokenServiceTest extends TestCase
{
    #[TestDox('Токен сгенерирован')]
    public function testTokenGenerated(): void
    {
        $authTokenService = $this->getAuthTokenService();
        $generatedToken = $authTokenService->generateAuthToken();

        self::assertInstanceOf(AuthToken::class, $generatedToken);
        self::assertInstanceOf(Uuid::class, $generatedToken->tokenId);
    }

    #[TestDox('Сформирован "объединенный" токен')]
    public function testConcatenatedTokenBuilt(): void
    {
        $authTokenService = $this->getAuthTokenService();
        $token = new AuthToken(new UuidV7(), uniqid());
        $concatenatedToken = $authTokenService->buildConcatenatedToken($token);

        self::assertNotEmpty($concatenatedToken);
    }

    #[TestDox('Токен из запроса успешно получен')]
    public function testTokenFromRequestSuccessfullyParsed(): void
    {
        $authTokenService = $this->getAuthTokenService();
        $token = new AuthToken(new UuidV7(), uniqid());
        $concatenatedToken = $authTokenService->buildConcatenatedToken($token);
        $parsedToken = $authTokenService->parseToken($concatenatedToken);

        self::assertInstanceOf(AuthToken::class, $parsedToken);
        self::assertInstanceOf(Uuid::class, $parsedToken->tokenId);
    }

    /**
     * @param non-empty-string $token
     */
    #[DataProvider('invalidTokens')]
    #[TestDox('Получен невалидный токен')]
    public function testInvalidTokenFromRequest(string $token): void
    {
        $authTokenService = $this->getAuthTokenService();
        self::expectException(TokenException::class);
        $authTokenService->parseToken($token);
    }

    public static function invalidTokens(): Iterator
    {
        yield 'Неверный формат' => ['1q2w3e4r5t'];

        yield 'Невалидный UUID токена' => ['1q2w3e4r-1q2w3e4r-1q2w3e4r-1q2w3e4r-1q2w3er-1234324qe'];

        yield 'Пустая строка' => [''];
    }

    private function getAuthTokenService(): AuthTokenService
    {
        return new AuthTokenService();
    }
}
