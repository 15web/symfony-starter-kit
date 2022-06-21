<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class RemoveTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        Task::create('Тестовая задача 1');
        Task::create('Тестовая задача 2');

        $tasks = Task::list();

        $task1Id = $tasks[0]['id'];
        $task2Id = $tasks[1]['id'];

        $response = self::request('POST', "/api/tasks/{$task1Id}/remove");
        self::assertSuccessBodyResponse($response);

        $tasks = Task::list();

        self::assertCount(1, $tasks);
        self::assertSame($task2Id, $tasks[0]['id']);
    }

    public function testNotFound(): void
    {
        Task::create('Тестовая задача 1');

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/remove");
        self::assertNotFoundResponse($response);
    }
}
