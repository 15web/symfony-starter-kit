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
 * @testdox Функциональный тест удаления сущности Task
 */
final class RemoveTaskTest extends ApiWebTestCase
{
    /**
     * @testdox Task удален
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        $task1Id = Task::createAndReturnId('Тестовая задача 1', $token);
        $task2Id = Task::createAndReturnId('Тестовая задача 2', $token);

        $response = self::request('POST', "/api/tasks/{$task1Id}/remove", token: $token);
        self::assertSuccessContentResponse($response);

        $tasks = Task::list($token);

        self::assertCount(1, $tasks);
        self::assertSame($task2Id, $tasks[0]['id']);
    }

    /**
     * @testdox Task не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен, невалидный токен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @testdox Доступ запрещен для пользователя не автора
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $token);

        self::assertNotFound($response);
    }
}
