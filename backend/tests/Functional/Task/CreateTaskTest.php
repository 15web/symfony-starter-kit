<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;

final class CreateTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::authFirst();
        $response = Task::create($taskName = 'Тестовая задача', $token);

        self::assertSuccessResponse($response);
        $taskInfo = self::jsonDecode($response->getContent());
        self::assertNotNull($taskInfo['id']);

        $tasks = Task::list($token);

        self::assertCount(1, $tasks);
        self::assertSame($taskName, $tasks[0]['taskName']);
        self::assertNotNull($tasks[0]['id']);
        self::assertSame($taskInfo['id'], $tasks[0]['id']);
        self::assertFalse($tasks[0]['isCompleted']);
    }

    public function testBadRequests(): void
    {
        $token = User::authFirst();

        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['taskName' => ''], $token);

        $badJson = '{"taskName"=1}';
        $response = self::request('POST', '/api/tasks/create', $badJson, token: $token, validateRequestSchema: false);
        self::assertBadRequest($response);
    }

    private function assertBadRequests(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/tasks/create', $body, token: $token, validateRequestSchema: false);
        self::assertBadRequest($response);
    }
}
