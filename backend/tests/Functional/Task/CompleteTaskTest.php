<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class CompleteTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        Task::create('Тестовая задача 1');
        $tasks = Task::list();
        $taskId = $tasks[0]['id'];

        $response = self::request('POST', "/api/tasks/{$taskId}/complete");
        self::assertSuccessBodyResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}");
        $task = self::jsonDecode($response->getContent());

        self::assertTrue($task['isCompleted']);
        self::assertNotNull($task['updatedAt']);
        self::assertNotNull($task['completedAt']);
    }

    public function testTaskHasAlreadyBeenCompleted(): void
    {
        Task::create('Тестовая задача 1');
        $tasks = Task::list();
        $taskId = $tasks[0]['id'];

        self::request('POST', "/api/tasks/{$taskId}/complete");

        $response = self::request('POST', "/api/tasks/{$taskId}/complete");
        self::assertBadRequestResponse($response);
    }

    public function testTaskNotFound(): void
    {
        Task::create('Тестовая задача 1');

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/complete");
        self::assertNotFoundResponse($response);
    }
}
