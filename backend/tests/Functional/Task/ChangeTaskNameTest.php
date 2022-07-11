<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

final class ChangeTaskNameTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::authFirst();
        Task::create('Тестовая задача 1', $token);
        $tasks = Task::list($token);
        $taskId = $tasks[0]['id'];

        $body = [];
        $body['taskName'] = $secondName = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body, false, $token);
        self::assertSuccessBodyResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}", null, false, $token);
        $task = self::jsonDecode($response->getContent());

        self::assertSame($secondName, $task['taskName']);
        self::assertNotNull($task['updatedAt']);
    }

    public function testNotFound(): void
    {
        $token = User::authFirst();
        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body, false, $token);
        self::assertNotFoundResponse($response);
    }

    public function testBadRequests(): void
    {
        $token = User::authFirst();
        $this->assertBadRequest([], $token);
        $this->assertBadRequest(['badKey'], $token);
        $this->assertBadRequest(['taskName' => ''], $token);
    }

    private function assertBadRequest(array $body, string $token): void
    {
        Task::create('Тестовая задача 1', $token);

        $tasks = Task::list($token);
        $taskId = $tasks[0]['id'];

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body, false, $token);
        self::assertBadRequestResponse($response);
    }
}
