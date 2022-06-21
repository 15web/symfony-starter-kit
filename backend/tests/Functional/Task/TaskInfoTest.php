<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class TaskInfoTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        Task::create($taskName = 'Тестовая задача 1');

        $tasks = Task::list();

        $taskId = $tasks[0]['id'];

        $response = self::request('GET', "/api/tasks/{$taskId}");
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
        Task::create('Тестовая задача 1');

        $taskId = (string) Uuid::v4();
        $response = self::request('GET', "/api/tasks/{$taskId}");
        self::assertNotFoundResponse($response);
    }
}
