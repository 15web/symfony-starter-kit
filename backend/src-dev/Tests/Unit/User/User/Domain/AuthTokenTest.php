<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\User\User\Domain;

use App\User\User\Domain\AuthToken;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserToken;
use App\User\User\Domain\UserTokenId;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

/**
 * @internal
 */
#[TestDox('Тест токена аутентификации')]
final class AuthTokenTest extends TestCase
{
    #[TestDox('Токен сгенерирован')]
    public function testTokenGenerated(): void
    {
        $generatedToken = AuthToken::generate(
            hashCost: 4,
        );

        self::assertInstanceOf(UserTokenId::class, $generatedToken->tokenId);
    }

    #[TestDox('Сформирован "объединенный" токен')]
    public function testConcatenatedTokenBuilt(): void
    {
        $token = new AuthToken(
            tokenId: new UserTokenId(),
            token: new UuidV7(),
            hashCost: 4,
        );

        $concatenatedToken = (string) $token;

        self::assertNotEmpty($concatenatedToken);
    }

    #[TestDox('Токен из запроса успешно получен')]
    public function testTokenFromRequestSuccessfullyParsed(): void
    {
        $token = new AuthToken(
            tokenId: new UserTokenId(),
            token: new UuidV7(),
            hashCost: 4,
        );

        $concatenatedToken = (string) $token;
        $parsedToken = AuthToken::createFromString(
            token: $concatenatedToken,
            hashCost: 4,
        );

        self::assertInstanceOf(UserTokenId::class, $parsedToken->tokenId);
    }

    #[TestDox('Создание хэша')]
    public function testHashMethod(): void
    {
        $token = new AuthToken(
            tokenId: new UserTokenId(),
            token: new UuidV7(),
            hashCost: 4,
        );

        $hash = $token->hash();

        self::assertStringStartsWith('$2y$04', $hash);
    }

    #[TestDox('Проверка хэша')]
    public function testVerifyMethod(): void
    {
        $tokenValue = new UuidV7();

        $token = new AuthToken(
            tokenId: new UserTokenId(),
            token: $tokenValue,
            hashCost: 4,
        );

        /** @var non-empty-string $hash */
        $hash = password_hash(
            password: (string) $tokenValue,
            algo: PASSWORD_BCRYPT,
            options: ['cost' => 4],
        );

        $userToken = new UserToken(
            id: new UserTokenId(),
            userId: new UserId(),
            hash: $hash,
        );

        self::assertTrue($token->verify($userToken));
    }

    /**
     * @param non-empty-string $token
     */
    #[DataProvider('invalidTokens')]
    #[TestDox('Получен невалидный токен')]
    public function testInvalidTokenFromRequest(string $token): void
    {
        $this->expectException(InvalidArgumentException::class);
        AuthToken::createFromString(
            token: $token,
            hashCost: 4,
        );
    }

    public static function invalidTokens(): Iterator
    {
        yield 'Неверный формат' => ['1q2w3e4r5t'];

        yield 'Невалидный UUID токена' => ['1q2w3e4r-1q2w3e4r-1q2w3e4r-1q2w3e4r-1q2w3er-1234324qe'];

        yield 'Пустая строка' => [''];
    }
}
