<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Response;

final class Task extends ApiWebTestCase
{
    public static function create(string $taskName, string $token): Response
    {
        $body = [];
        $body['taskName'] = $taskName;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request('POST', '/api/tasks/create', $body, false, $token);
    }

    public static function list(string $token): array
    {
        $response = self::request('GET', '/api/tasks', null, false, $token);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        return self::jsonDecode($response->getContent());
    }
}
