<?php

declare(strict_types=1);

namespace App\Ping\Http;

use App\Infrastructure\Response\ApiObjectResponse;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Эндпоинт для пинга проекта.
 */
#[Route(path: '/ping', name: 'ping', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class PingHttpAction
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function __invoke(): ApiObjectResponse
    {
        /** @var non-empty-string $result */
        $result = $this->connection->fetchOne("select 'pong'");

        return new ApiObjectResponse(new Pong($result));
    }
}
