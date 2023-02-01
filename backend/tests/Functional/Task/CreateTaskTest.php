<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Создание задачи
 */
final class CreateTaskTest extends ApiWebTestCase
{
    /**
     * @testdox Задача создана
     */
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = Task::create($taskName = 'Тестовая задача', $token);

        self::assertSuccessResponse($response);
        $taskInfo = self::jsonDecode($response->getContent());
        self::assertNotNull($taskInfo['id']);

        $response = Task::list($token);
        $tasks = $response['data'];

        self::assertCount(1, $tasks);
        self::assertSame($taskName, $tasks[0]['taskName']);
        self::assertNotNull($tasks[0]['id']);
        self::assertSame($taskInfo['id'], $tasks[0]['id']);
        self::assertFalse($tasks[0]['isCompleted']);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $response = Task::create('Тестовая задача', $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @testdox Неверный запрос
     */
    public function testBadRequests(): void
    {
        $token = User::auth();

        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['taskName' => ''], $token);

        $badJson = '{"taskName"=1}';
        $response = self::request('POST', '/api/tasks/create', $badJson, token: $token, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }

    private function assertBadRequests(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/tasks/create', $body, token: $token, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }
}
