<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Infrastructure\Request;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[TestDox('Заголовок Accept')]
final class AcceptHeaderTest extends WebTestCase
{
    /**
     * @param array<string, string> $headers
     */
    #[DataProvider('validHeader')]
    #[TestDox('Допустимые заголовки')]
    public function testValidHeaders(array $headers): void
    {
        self::createClient()->request(
            method: Request::METHOD_GET,
            uri: '/api/ping',
            server: $headers,
        );

        self::assertResponseIsSuccessful();
    }

    public static function validHeader(): Iterator
    {
        yield 'Пустые заголовки' => [[]];

        yield 'Любой MIME тип' => [['HTTP_ACCEPT' => '*/*']];

        yield 'Accept application/*' => [['HTTP_ACCEPT' => 'application/*']];

        yield 'Accept application/json' => [['HTTP_ACCEPT' => 'application/json']];

        yield 'Accept text/html and application/json' => [['HTTP_ACCEPT' => 'text/html, application/json']];
    }

    /**
     * @param array<string, string> $headers
     */
    #[DataProvider('notValidAcceptHeader')]
    #[TestDox('Недопустимые заголовки Accept')]
    public function testInvalidHeaders(array $headers): void
    {
        self::createClient()->request(
            method: Request::METHOD_GET,
            uri: '/api/ping',
            server: $headers,
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public static function notValidAcceptHeader(): Iterator
    {
        yield 'Accept text/html' => [['HTTP_ACCEPT' => 'text/html']];

        yield 'Accept application/xml' => [['HTTP_ACCEPT' => 'application/xml']];
    }
}
