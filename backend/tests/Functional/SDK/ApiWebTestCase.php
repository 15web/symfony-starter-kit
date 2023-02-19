<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use App\Infrastructure\OpenApiValidateSubscriber;
use Iterator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

abstract class ApiWebTestCase extends WebTestCase
{
    private static ?KernelBrowser $client = null;

    final public static function request(
        string $method,
        string $uri,
        ?string $body = null,
        bool $newClient = false,
        ?string $token = null,
        bool $disableValidateRequestSchema = false,
        bool $disableValidateResponseSchema = false,
    ): Response {
        Assert::notEmpty($method);
        Assert::notEmpty($uri);

        if (self::$client === null || $newClient) {
            self::$client = self::createClient();
        }

        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($token !== null) {
            $headers['HTTP_X_AUTH_TOKEN'] = $token;
        }

        self::$client->xmlHttpRequest($method, $uri, [
            // передаем признак в OpenApiValidateSubscriber что не нужно проверять запрос
            OpenApiValidateSubscriber::DISABLE_VALIDATE_REQUEST_KEY => $disableValidateRequestSchema,
            // передаем признак в OpenApiValidateSubscriber что не нужно проверять ответ
            OpenApiValidateSubscriber::DISABLE_VALIDATE_RESPONSE_KEY => $disableValidateResponseSchema,
        ], [], $headers, $body);

        return self::$client->getResponse();
    }

    final public static function jsonDecode(string|bool $content): array
    {
        if (\is_bool($content)) {
            return [];
        }

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    final public static function assertSuccessResponse(Response $response): void
    {
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    final public static function assertSuccessContentResponse(Response $response): void
    {
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        static::assertTrue($response['success']);
    }

    final public static function assertBadRequest(Response $response): void
    {
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    final public static function assertApiError(Response $response, int $apiErrorCode): void
    {
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        static::assertTrue($response['error']);
        static::assertSame($response['code'], $apiErrorCode);
        static::assertNotEmpty($response['errorMessage']);
    }

    final public static function assertAccessDenied(Response $response): void
    {
        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    final public static function assertNotFound(Response $response): void
    {
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    final public static function assertForbidden(Response $response): void
    {
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    final public function notValidTokenDataProvider(): Iterator
    {
        yield 'пустая строка' => [''];

        yield 'текст' => ['любая строка'];

        yield 'случайный идентификатор' => [(string) Uuid::v4()];
    }
}
