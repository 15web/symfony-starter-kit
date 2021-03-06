<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

final class CompleteTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::authFirst();

        Task::create('Тестовая задача 1', $token);
        $tasks = Task::list($token);
        $taskId = $tasks[0]['id'];

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        $task = self::jsonDecode($response->getContent());

        self::assertTrue($task['isCompleted']);
        self::assertNotNull($task['updatedAt']);
        self::assertNotNull($task['completedAt']);
    }

    public function testTaskHasAlreadyBeenCompleted(): void
    {
        $token = User::authFirst();

        Task::create('Тестовая задача 1', $token);
        $tasks = Task::list($token);
        $taskId = $tasks[0]['id'];

        self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertBadRequest($response);
    }

    public function testTaskNotFound(): void
    {
        $token = User::authFirst();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertNotFound($response);
    }
}
