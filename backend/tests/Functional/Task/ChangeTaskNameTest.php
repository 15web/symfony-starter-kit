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
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = $secondName = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body, token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        $task = self::jsonDecode($response->getContent());

        self::assertSame($secondName, $task['taskName']);
        self::assertNotNull($task['updatedAt']);
    }

    public function testNotFound(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/update-task-name", $body, token: $token);
        self::assertNotFound($response);
    }

    public function testBadRequests(): void
    {
        $token = User::auth();
        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['taskName' => ''], $token);
    }

    private function assertBadRequests(array $body, string $token): void
    {
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            'POST',
            "/api/tasks/{$taskId}/update-task-name",
            $body,
            token: $token,
            validateRequestSchema: false
        );
        self::assertBadRequest($response);
    }
}
