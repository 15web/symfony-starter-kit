<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Выполнение задачи
 */
final class CompleteTaskTest extends ApiWebTestCase
{
    /**
     * @testdox Задача выполнена
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        $task = self::jsonDecode($response->getContent());

        self::assertTrue($task['isCompleted']);
        self::assertNotNull($task['updatedAt']);
        self::assertNotNull($task['completedAt']);
    }

    /**
     * @testdox Нельзя повторно выполнить задачу
     */
    public function testTaskHasAlreadyBeenCompleted(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertBadRequest($response);
    }

    /**
     * @testdox Задача не найдена
     */
    public function testTaskNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request(
            'POST',
            "/api/tasks/{$taskId}/complete",
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    /**
     * @testdox Выполнить задачу может только автор
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request(
            'POST',
            "/api/tasks/{$taskId}/complete",
            token: $token,
        );

        self::assertNotFound($response);
    }
}
