<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

final class CreateTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::authFirst();
        $response = Task::create($taskName = 'Тестовая задача', $token);

        self::assertSuccessResponse($response);
        self::assertSuccessBodyResponse($response);

        $tasks = Task::list($token);

        self::assertCount(1, $tasks);
        self::assertSame($taskName, $tasks[0]['taskName']);
        self::assertNotNull($tasks[0]['id']);
        self::assertFalse($tasks[0]['isCompleted']);
    }

    public function testBadRequests(): void
    {
        $token = User::authFirst();

        $this->assertBadRequest([], $token);
        $this->assertBadRequest(['badKey'], $token);
        $this->assertBadRequest(['taskName' => ''], $token);

        $badJson = '{"taskName"=1}';
        $response = self::request('POST', '/api/tasks/create', $badJson, false, $token);
        self::assertBadRequestResponse($response);
    }

    private function assertBadRequest(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/tasks/create', $body, false, $token);
        self::assertBadRequestResponse($response);
    }
}
