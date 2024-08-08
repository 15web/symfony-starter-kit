<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\SDK;

use App\Infrastructure\Response\ResponseStatus;
use Dev\Infrastructure\EventSubscriber\OpenApiValidateSubscriber;
use Doctrine\DBAL\Connection;
use Iterator;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Содержит общие проверки на валидность (неверный токен, ошибка запроса, запрещенный доступ)
 */
abstract class ApiWebTestCase extends WebTestCase
{
    private static KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        self::$client = self::createClient();

        // https://symfony.com/doc/current/testing.html#multiple-requests-in-one-test
        self::$client->disableReboot();
    }

    /**
     * Отправить запрос
     *
     * @param string $method HTTP метод запроса
     * @param string $uri Адрес запроса
     * @param string|null $body Тело запроса
     * @param string|null $token Токен авторизации
     * @param bool $validateRequestSchema Валидация запроса
     * @param bool $validateResponseSchema Валидация ответа
     * @param bool $resetRateLimiter Сброс состояния рейт лимитера после запроса
     */
    final public static function request(
        string $method,
        string $uri,
        ?string $body = null,
        ?string $token = null,
        bool $validateRequestSchema = true,
        bool $validateResponseSchema = true,
        bool $resetRateLimiter = true
    ): Response {
        Assert::notEmpty($method);
        Assert::notEmpty($uri);

        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($token !== null) {
            $headers['HTTP_X_AUTH_TOKEN'] = $token;
        }

        self::$client->request($method, $uri, [
            // передаем признак в OpenApiValidateSubscriber что не нужно проверять запрос
            OpenApiValidateSubscriber::VALIDATE_REQUEST_KEY => $validateRequestSchema,
            // передаем признак в OpenApiValidateSubscriber что не нужно проверять ответ
            OpenApiValidateSubscriber::VALIDATE_RESPONSE_KEY => $validateResponseSchema,
        ], [], $headers, $body);

        if ($resetRateLimiter) {
            /** @var Psr6CacheClearer $clearer */
            $clearer = self::getContainer()->get('cache.global_clearer');
            $clearer->clearPool('cache.rate_limiter');
        }

        return self::$client->getResponse();
    }

    final public static function getConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        return $connection;
    }

    /**
     * @return array<mixed>
     */
    final public static function jsonDecode(bool|string $content): array
    {
        if (\is_bool($content)) {
            return [];
        }

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    final public static function assertSuccessResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    final public static function assertSuccessContentResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var array{
         *     data: array{
         *     status: string|null
         *    },
         *    status: string|null
         * } $responseContent */
        $responseContent = self::jsonDecode($response->getContent());
        $successResponse = ResponseStatus::Success;

        self::assertSame($responseContent['status'], $successResponse->value);
    }

    final public static function assertBadRequest(Response $response): void
    {
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    final public static function assertApiError(Response $response, int $apiErrorCode): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /**
         * @var array{
         *     data: array{
         *         code: int,
         *         message: ?string,
         *         errors: array <int, array{}>
         *     }
         * } $errorResponse
         */
        $errorResponse = self::jsonDecode($response->getContent());

        self::assertSame($errorResponse['data']['code'], $apiErrorCode);
        self::assertNotEmpty($errorResponse['data']['message']);
        self::assertNotEmpty($errorResponse['data']['errors']);
    }

    final public static function assertAccessDenied(Response $response): void
    {
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    final public static function assertNotFound(Response $response): void
    {
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    final public static function assertForbidden(Response $response): void
    {
        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    final public static function assertTooManyRequests(Response $response): void
    {
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    final public static function notValidTokenDataProvider(): Iterator
    {
        yield 'пустая строка' => [''];

        yield 'текст' => ['любая строка'];

        yield 'случайный идентификатор' => [(string) Uuid::v7()];
    }
}
