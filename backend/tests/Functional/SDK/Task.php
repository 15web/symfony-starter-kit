<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class Task extends ApiWebTestCase
{
    public static function create(string $taskName, string $token): Response
    {
        $body = [];
        $body['taskName'] = $taskName;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request(Request::METHOD_POST, '/api/tasks', $body, token: $token);
    }

    public static function createAndReturnId(string $taskName, string $token): string
    {
        $response = self::create($taskName, $token);

        /** @var array{
         *     id: string
         * } $taskInfo */
        $taskInfo = self::jsonDecode($response->getContent());

        return $taskInfo['id'];
    }

    /**
     * @return array{
     *     data: array<int, array{
     *          id: string,
     *          taskName: string,
     *          isCompleted: bool
     *     }>,
     *     pagination: array{total: int},
     * }
     */
    public static function list(string $token, int $limit = 10, int $offset = 0): array
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        $uri = '/api/tasks?'.http_build_query($params);

        $response = self::request(Request::METHOD_GET, $uri, token: $token);

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array<int, array{
         *          id: string,
         *          taskName: string,
         *          isCompleted: bool
         *     }>,
         *     pagination: array{total: int},
         * } $tasks
         */
        $tasks = self::jsonDecode($response->getContent());

        return $tasks;
    }
}
