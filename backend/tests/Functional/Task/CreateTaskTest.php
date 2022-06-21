<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;

final class CreateTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $response = Task::create($taskName = 'Тестовая задача');

        self::assertSuccessResponse($response);
        self::assertSuccessBodyResponse($response);

        $tasks = Task::list();

        self::assertCount(1, $tasks);
        self::assertSame($taskName, $tasks[0]['taskName']);
        self::assertNotNull($tasks[0]['id']);
        self::assertFalse($tasks[0]['isCompleted']);
    }

    public function testBadRequests(): void
    {
        $this->assertBadRequest([]);
        $this->assertBadRequest(['badKey']);
        $this->assertBadRequest(['taskName' => '']);

        $badJson = '{"taskName"=1}';
        $response = self::request('POST', '/api/tasks/create', $badJson);
        self::assertBadRequestResponse($response);
    }

    private function assertBadRequest(array $body): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/tasks/create', $body);
        self::assertBadRequestResponse($response);
    }
}
