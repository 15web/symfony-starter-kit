<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class ApiWebTestCase extends WebTestCase
{
    private const OPENAPI_YAML_FILE = './openapi.yaml';

    private static ?KernelBrowser $client = null;
    private static ?RequestValidator $requestValidator = null;
    private static ?ResponseValidator $responseValidator = null;

    public static function request(
        string $method,
        string $uri,
        ?string $body = null,
        bool $newClient = false,
        ?string $token = null,
        bool $validateRequestSchema = true,
        bool $validateResponseSchema = true,
    ): Response {

        Assert::notEmpty($method);
        Assert::notEmpty($uri);

        if (self::$client === null || $newClient === true) {
            self::$client = self::createClient();
        }

        if (self::$requestValidator === null || self::$responseValidator === null) {
            self::createValidators();
        }

        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($token !== null) {
            $headers['HTTP_X_AUTH_TOKEN'] = $token;
        }

        self::$client->xmlHttpRequest($method, $uri, [], [], $headers, $body);

        if ($validateRequestSchema === true) {
            self::validateRequestSchema(self::$client->getRequest());
        }

        $response = self::$client->getResponse();
        if ($validateResponseSchema === true) {
            self::validateResponseSchema($method, $uri, $response);
        }

        return $response;
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

    public static function assertSuccessContentResponse(Response $response): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        self::assertTrue($response['success']);
    }

    public static function assertBadRequest(Response $response): void
    {
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public static function assertApiError(Response $response, int $apiErrorCode): void
    {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = self::jsonDecode($response->getContent());
        self::assertTrue($response['error']);
        self::assertSame($response['code'], $apiErrorCode);
        self::assertNotEmpty($response['errorMessage']);
    }

    public static function assertAccessDenied(Response $response): void
    {
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public static function assertNotFound(Response $response): void
    {
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public static function assertForbidden(Response $response): void
    {
        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    private static function createValidators(): void
    {
        $validatorBuilder = (new ValidatorBuilder())->fromYamlFile(self::OPENAPI_YAML_FILE);

        self::$requestValidator = $validatorBuilder->getRequestValidator();
        self::$responseValidator = $validatorBuilder->getResponseValidator();
    }

    private static function validateRequestSchema(Request $request): void
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrRequest = $psrHttpFactory->createRequest($request);

        self::$requestValidator->validate($psrRequest);
    }

    private static function validateResponseSchema(string $method, string $uri, Response $response): void
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrResponse = $psrHttpFactory->createResponse($response);

        self::$responseValidator->validate(new OperationAddress($uri, strtolower($method)), $psrResponse);
    }
}
