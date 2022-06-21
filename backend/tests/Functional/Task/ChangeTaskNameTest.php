<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class ChangeTaskNameTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        Task::create('Тестовая задача 1');
        $tasks = Task::list();
        $taskId = $tasks[0]['id'];

        $body = [];
        $body['taskName'] = $secondName = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body);
        self::assertSuccessBodyResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}");
        $task = self::jsonDecode($response->getContent());

        self::assertSame($secondName, $task['taskName']);
        self::assertNotNull($task['updatedAt']);
    }

    public function testNotFound(): void
    {
        Task::create('Тестовая задача 1');

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body);
        self::assertNotFoundResponse($response);
    }

    public function testBadRequests(): void
    {
        $this->assertBadRequest([]);
        $this->assertBadRequest(['badKey']);
        $this->assertBadRequest(['taskName' => '']);
    }

    private function assertBadRequest(array $body): void
    {
        Task::create('Тестовая задача 1');

        $tasks = Task::list();
        $taskId = $tasks[0]['id'];

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body);
        self::assertBadRequestResponse($response);
    }
}
