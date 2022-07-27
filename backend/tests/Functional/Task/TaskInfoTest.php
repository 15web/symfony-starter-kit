<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

final class TaskInfoTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::authFirst();

        $taskId= Task::createAndReturnId($taskName = 'Тестовая задача 1', $token);

        $tasks = Task::list($token);

        self::assertCount(1, $tasks);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        self::assertSuccessResponse($response);

        $task = self::jsonDecode($response->getContent());

        self::assertNotNull($task['id']);
        self::assertSame($taskName, $task['taskName']);
        self::assertFalse($task['isCompleted']);
        self::assertNotNull($task['createdAt']);
        self::assertNull($task['completedAt']);
        self::assertNull($task['updatedAt']);
    }

    public function testNotFound(): void
    {
        $token = User::authFirst();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        self::assertNotFound($response);
    }
}
