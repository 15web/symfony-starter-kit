<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Функциональный тест получения списка сущностей Task
 */
final class TaskListTest extends ApiWebTestCase
{
    /**
     * @testdox Получение списка сущностей Task
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $tasks = Task::list($token);

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertNotNull($task['id']);
            self::assertFalse($task['isCompleted']);
            self::assertNotNull($task['taskName']);
        }
    }

    /**
     * @testdox Доступ запрещен для пользователя не автора
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId3 = Task::createAndReturnId($taskName3 = 'Тестовая задача 3', $tokenSecond);
        $taskId4 = Task::createAndReturnId($taskName4 = 'Тестовая задача 4', $tokenSecond);

        $tasks = Task::list($token);

        foreach ($tasks as $task) {
            self::assertNotSame($task['id'], $taskId3);
            self::assertNotSame($task['id'], $taskId4);
            self::assertNotSame($task['taskName'], $taskName3);
            self::assertNotSame($task['taskName'], $taskName4);
        }
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен, невалидный токен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request('GET', '/api/tasks', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
