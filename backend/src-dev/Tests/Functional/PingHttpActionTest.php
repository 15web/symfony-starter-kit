<?php

declare(strict_types=1);

namespace Dev\Tests\Functional;

use App\Infrastructure\Response\ResponseStatus;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Пинг приложения')]
final class PingHttpActionTest extends ApiWebTestCase
{
    #[TestDox('Получение ответа')]
    public function testSuccess(): void
    {
        $response = self::request(Request::METHOD_GET, '/api/ping');

        /** @var array{
         *     data: array{result: string},
         *     status: string
         * } $data */
        $data = self::jsonDecode($response->getContent());

        self::assertSuccessResponse($response);
        self::assertSame('pong', $data['data']['result']);
        self::assertSame(ResponseStatus::Success->value, $data['status']);
    }
}
