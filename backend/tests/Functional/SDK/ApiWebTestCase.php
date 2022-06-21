<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiWebTestCase extends WebTestCase
{
    private static ?KernelBrowser $client = null;

    public static function request(
        string $method,
        string $uri,
        ?string $body = null,
        bool $newClient = false,
        ?string $token = null,
    ): Response {
        if (self::$client === null || $newClient === true) {
            self::$client = self::createClient();
        }

        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($token !== null) {
            $headers['HTTP_X_AUTH_TOKEN'] = $token;
        }

        self::$client->xmlHttpRequest($method, $uri, [], [], $headers, $body);

        return self::$client->getResponse();
    }

    public static function jsonDecode(string|bool $content): array
    {
        if (\is_bool($content)) {
            return [];
        }

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    public static function assertSuccessResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public static function assertSuccessBodyResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        self::assertTrue($response['success']);
    }

    public static function assertBadRequestResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public static function assertBadResponseResponse(Response $response, int $apiErrorCode): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        self::assertTrue($response['error']);
        self::assertSame($response['code'], $apiErrorCode);
    }

    public static function assertAccessDeniedResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public static function assertNotFoundResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public static function assertForbiddenResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public static function assertUnauthorizedResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
